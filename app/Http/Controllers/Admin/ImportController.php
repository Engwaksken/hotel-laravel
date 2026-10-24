<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{DataImportRun, Room, RoomType};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    private function pid(Request $request): int
    {
        abort_unless($request->user()->canManageBranch(), 403);
        abort_unless($request->user()->property_id, 403, 'Your account is not assigned to a hotel branch.');

        return (int) $request->user()->property_id;
    }

    public function index(Request $request)
    {
        $pid = $this->pid($request);

        return view('admin.imports', [
            'runs' => DataImportRun::where('property_id', $pid)->latest()->limit(30)->get(),
        ]);
    }

    public function roomTemplate()
    {
        return response(
            "room_number,room_type_code,name,building,floor\n101,STD,Standard 101,Main,1\n",
            200,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="rooms-template.csv"',
            ]
        );
    }

    public function rateTemplate()
    {
        return response(
            "room_type_code,base_rate,max_adults,max_children\nSTD,150000,2,1\n",
            200,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="room-rates-template.csv"',
            ]
        );
    }

    public function rooms(Request $request)
    {
        $pid = $this->pid($request);
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:5120']);

        return $this->importCsv($request, $pid, 'rooms', function (array $row) use ($pid) {
            $type = RoomType::where('property_id', $pid)
                ->where('code', $row['room_type_code'] ?? '')
                ->firstOrFail();

            Room::updateOrCreate(
                ['property_id' => $pid, 'room_number' => $row['room_number']],
                [
                    'room_type_id' => $type->id,
                    'name' => $row['name'] ?? null,
                    'building' => $row['building'] ?? null,
                    'floor' => $row['floor'] ?? null,
                    'is_active' => true,
                ]
            );
        });
    }

    public function rates(Request $request)
    {
        $pid = $this->pid($request);
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:5120']);

        return $this->importCsv($request, $pid, 'room_rates', function (array $row) use ($pid) {
            $type = RoomType::where('property_id', $pid)
                ->where('code', $row['room_type_code'] ?? '')
                ->firstOrFail();

            $type->update([
                'base_rate' => (float) $row['base_rate'],
                'max_adults' => (int) ($row['max_adults'] ?? $type->max_adults),
                'max_children' => (int) ($row['max_children'] ?? $type->max_children),
            ]);
        });
    }

    private function importCsv(Request $request, int $pid, string $type, callable $callback)
    {
        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        $headers = array_map('trim', fgetcsv($handle) ?: []);
        $total = $ok = $bad = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            while (($values = fgetcsv($handle)) !== false) {
                $total++;

                try {
                    $row = array_combine($headers, array_pad($values, count($headers), null));
                    $callback($row);
                    $ok++;
                } catch (\Throwable $e) {
                    $bad++;
                    $errors[] = [
                        'row' => $total + 1,
                        'message' => mb_substr($e->getMessage(), 0, 240),
                    ];
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        } finally {
            fclose($handle);
        }

        DataImportRun::create([
            'property_id' => $pid,
            'user_id' => $request->user()->id,
            'type' => $type,
            'original_name' => $request->file('file')->getClientOriginalName(),
            'rows_total' => $total,
            'rows_imported' => $ok,
            'rows_failed' => $bad,
            'status' => $bad ? 'completed_with_errors' : 'completed',
            'errors' => $errors,
        ]);

        return back()->with('success', "Import complete: {$ok} imported, {$bad} failed.");
    }
}
