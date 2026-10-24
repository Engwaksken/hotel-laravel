<?php

namespace App\Http\Controllers;

use App\Models\{CorporateAccount, Employee, HotelEvent, Promotion, Room, RoomType, Store, Supplier, TravelAgent};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ManagedEntryController extends Controller
{
    /** @var array<string,array<string,mixed>> */
    private array $resources = [
        'rooms' => [
            'model' => Room::class,
            'title' => 'Room',
            'fields' => ['room_number','name','building','floor','status','housekeeping_status','notes','is_active'],
        ],
        'room-types' => [
            'model' => RoomType::class,
            'title' => 'Room Type',
            'fields' => ['name','code','description','base_occupancy','max_adults','max_children','base_rate','is_active'],
        ],
        'suppliers' => [
            'model' => Supplier::class,
            'title' => 'Supplier',
            'fields' => ['name','contact_person','phone','email','tin','address','bank_name','bank_account','credit_days','is_active'],
        ],
        'stores' => [
            'model' => Store::class,
            'title' => 'Store',
            'fields' => ['name','code','type','location','is_active'],
        ],
        'employees' => [
            'model' => Employee::class,
            'title' => 'Employee',
            'fields' => ['first_name','other_names','last_name','phone','email','employment_type','hire_date','basic_salary','tax_residency','nssf_eligible','status'],
        ],
        'companies' => [
            'model' => CorporateAccount::class,
            'title' => 'Corporate Account',
            'fields' => ['code','name','tin','phone','email','address','contact_person','credit_limit','credit_days','default_discount_percent','status'],
        ],
        'agents' => [
            'model' => TravelAgent::class,
            'title' => 'Travel Agent',
            'fields' => ['code','name','contact_person','phone','email','commission_percent','credit_limit','credit_days','status'],
        ],
        'events' => [
            'model' => HotelEvent::class,
            'title' => 'Event',
            'fields' => ['title','event_type','contact_name','contact_phone','contact_email','starts_at','ends_at','expected_guests','estimated_total','deposit_required','requirements','status'],
        ],
        'promotions' => [
            'model' => Promotion::class,
            'title' => 'Promotion',
            'fields' => ['name','code','type','value','minimum_spend','maximum_discount','starts_at','ends_at','usage_limit','per_guest_limit','is_active'],
        ],
    ];

    public function edit(Request $request, string $resource, int $id)
    {
        [$config, $record] = $this->resolve($request, $resource, $id);

        $fields = collect($config['fields'])->map(function (string $field) use ($record) {
            if (!Schema::hasColumn($record->getTable(), $field)) {
                return null;
            }

            $value = $record->{$field};

            if ($value instanceof \DateTimeInterface) {
                $value = $value->format(str_ends_with($field, '_at') ? 'Y-m-d\TH:i' : 'Y-m-d');
            }

            return [
                'name' => $field,
                'label' => str($field)->replace('_', ' ')->title()->toString(),
                'type' => $this->fieldType($field),
                'value' => $value,
            ];
        })->filter()->values();

        return response()->json([
            'resource' => $resource,
            'title' => $config['title'],
            'id' => $record->getKey(),
            'fields' => $fields,
        ]);
    }

    public function update(Request $request, string $resource, int $id)
    {
        [$config, $record] = $this->resolve($request, $resource, $id);
        $payload = $request->only($config['fields']);

        foreach ($payload as $key => $value) {
            if (!Schema::hasColumn($record->getTable(), $key)) {
                unset($payload[$key]);
                continue;
            }
            if ($value === '') {
                $payload[$key] = null;
            }
        }

        if (array_key_exists('is_active', $payload)) {
            $payload['is_active'] = $request->boolean('is_active');
        }

        $record->fill($payload);
        $record->save();

        $message = $config['title'].' updated successfully.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return redirect()->to($request->input('_return', url()->previous()))
            ->with('success', $message);
    }

    public function destroy(Request $request, string $resource, int $id)
    {
        [$config, $record] = $this->resolve($request, $resource, $id);

        try {
            $record->delete();
        } catch (\Throwable $e) {
            report($e);
            $message = 'This '.$config['title'].' cannot be deleted because it is linked to other records. Deactivate it instead.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }
            return back()->with('error', $message);
        }

        $message = $config['title'].' deleted successfully.';
        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('success', $message);
    }

    private function fieldType(string $field): string
    {
        if ($field === 'is_active') return 'checkbox';
        if (str_ends_with($field, '_at') || in_array($field, ['starts_at','ends_at'], true)) return 'datetime-local';
        if (str_ends_with($field, '_date') || in_array($field, ['hire_date'], true)) return 'date';
        if (in_array($field, ['description','address','requirements','notes'], true)) return 'textarea';
        if (in_array($field, [
            'base_occupancy','max_adults','max_children','base_rate','credit_limit','credit_days',
            'default_discount_percent','commission_percent','expected_guests','estimated_total',
            'deposit_required','value','minimum_spend','maximum_discount','usage_limit','per_guest_limit',
            'basic_salary','floor'
        ], true)) return 'number';
        if ($field === 'email') return 'email';
        if (in_array($field, ['phone','contact_phone'], true)) return 'tel';
        return 'text';
    }

    private function resolve(Request $request, string $resource, int $id): array
    {
        $user = $request->user();
        abort_unless($user && method_exists($user, 'canManageEntries') && $user->canManageEntries(), 403);

        $config = $this->resources[$resource] ?? null;
        abort_unless($config, 404);

        /** @var Model $record */
        $record = $config['model']::query()->findOrFail($id);

        $propertyId = Schema::hasColumn($record->getTable(), 'property_id')
            ? ($record->property_id !== null ? (int) $record->property_id : null)
            : null;

        if ($propertyId !== null) {
            abort_unless($user->canManageEntryProperty($propertyId), 404);
        } elseif (!$user->isSuperAdmin() && $user->role !== 'hotel_owner' && $user->role !== 'general_manager' && $user->role !== 'admin') {
            abort(403);
        }

        return [$config, $record];
    }
}
