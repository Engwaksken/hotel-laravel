<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkDataController extends Controller
{
    private const DEFINITIONS = [
        'rooms' => ['table' => 'rooms', 'columns' => ['room_number', 'name', 'building', 'floor', 'status', 'housekeeping_status', 'notes']],
        'guests' => ['table' => 'guests', 'columns' => ['full_name', 'email', 'phone', 'nationality', 'address', 'notes']],
        'inventory' => ['table' => 'inventory_items', 'columns' => ['name', 'sku', 'category', 'unit', 'reorder_level', 'cost_price', 'selling_price', 'notes']],
    ];

    public function template(Request $request, string $module): StreamedResponse
    {
        $definition = $this->definition($module);

        return response()->streamDownload(function () use ($definition): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, $definition['columns']);
            fclose($handle);
        }, $module.'-template.csv', ['Content-Type' => 'text/csv']);
    }

    public function export(Request $request, string $module): StreamedResponse
    {
        $definition = $this->definition($module);
        $columns = array_values(array_filter(
            $definition['columns'],
            fn ($column) => Schema::hasColumn($definition['table'], $column)
        ));

        $rows = DB::table($definition['table'])->select($columns)->get();

        return response()->streamDownload(function () use ($columns, $rows): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, $columns);

            foreach ($rows as $row) {
                fputcsv($handle, array_map(
                    fn ($column) => $row->{$column} ?? '',
                    $columns
                ));
            }

            fclose($handle);
        }, $module.'-export-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request, string $module): RedirectResponse
    {
        $definition = $this->definition($module);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'rb');
        abort_unless($handle !== false, 422, 'Unable to read CSV file.');

        $header = fgetcsv($handle) ?: [];
        $header = array_map(fn ($value) => trim((string) $value), $header);

        $allowed = array_values(array_filter(
            $definition['columns'],
            fn ($column) => Schema::hasColumn($definition['table'], $column)
        ));

        $count = 0;

        DB::transaction(function () use ($handle, $header, $allowed, $definition, &$count): void {
            while (($row = fgetcsv($handle)) !== false) {
                $payload = [];

                foreach ($header as $index => $column) {
                    if (in_array($column, $allowed, true)) {
                        $payload[$column] = $row[$index] ?? null;
                    }
                }

                if ($payload === []) {
                    continue;
                }

                if (Schema::hasColumn($definition['table'], 'created_at')) {
                    $payload['created_at'] = now();
                }
                if (Schema::hasColumn($definition['table'], 'updated_at')) {
                    $payload['updated_at'] = now();
                }

                DB::table($definition['table'])->insert($payload);
                $count++;
            }
        });

        fclose($handle);

        return back()->with('success', $count.' row(s) imported successfully.');
    }

    private function definition(string $module): array
    {
        abort_unless(isset(self::DEFINITIONS[$module]), 404, 'Unsupported bulk import module.');

        $definition = self::DEFINITIONS[$module];
        abort_unless(Schema::hasTable($definition['table']), 404, 'Module table is not available.');

        return $definition;
    }
}
