<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\WebDataModuleService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class WebDataToolsController extends Controller
{
    public function index(
        Request $request,
        WebDataModuleService $modules
    ): View {
        $this->authorise($request);

        return view('admin.bulk.index', [
            'modules' => collect($modules->modules())
                ->filter(fn ($module) =>
                    ! empty($module['table'])
                    && Schema::hasTable($module['table'])
                ),
        ]);
    }

    public function template(
        Request $request,
        string $module,
        WebDataModuleService $modules
    ): StreamedResponse {
        $this->authorise($request);

        $definition = $modules->get($module);

        return response()->streamDownload(
            function () use ($definition): void {
                $handle = fopen('php://output', 'wb');
                fputcsv($handle, $definition['columns']);
                fclose($handle);
            },
            $module.'-template.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    public function csv(
        Request $request,
        string $module,
        WebDataModuleService $modules
    ): StreamedResponse {
        $definition = $modules->get($module);

        $rows = $this->query(
            $request,
            $definition['table']
        )
            ->select($definition['columns'])
            ->get();

        return response()->streamDownload(
            function () use ($rows, $definition): void {
                $handle = fopen('php://output', 'wb');

                fwrite(
                    $handle,
                    "\xEF\xBB\xBF"
                );

                fputcsv(
                    $handle,
                    $definition['columns']
                );

                foreach ($rows as $row) {
                    fputcsv(
                        $handle,
                        array_map(
                            fn (string $column) =>
                                $row->{$column} ?? '',
                            $definition['columns']
                        )
                    );
                }

                fclose($handle);
            },
            $module.'-'.now()->format('Ymd-His').'.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    public function pdf(
        Request $request,
        string $module,
        WebDataModuleService $modules
    ) {
        $definition = $modules->get($module);

        $rows = $this->query(
            $request,
            $definition['table']
        )
            ->select($definition['columns'])
            ->get();

        return Pdf::loadView(
            'exports.table',
            [
                'title' => $definition['label'],
                'columns' => $definition['columns'],
                'rows' => $rows,
            ]
        )
            ->setPaper('a4', 'landscape')
            ->stream(
                $module.'-'.now()->format('Ymd-His').'.pdf'
            );
    }

    public function print(
        Request $request,
        string $module,
        WebDataModuleService $modules
    ): View {
        $definition = $modules->get($module);

        $rows = $this->query(
            $request,
            $definition['table']
        )
            ->select($definition['columns'])
            ->get();

        return view('exports.print', [
            'title' => $definition['label'],
            'columns' => $definition['columns'],
            'rows' => $rows,
        ]);
    }

    public function import(
        Request $request,
        string $module,
        WebDataModuleService $modules
    ): RedirectResponse {
        $this->authorise($request);

        $definition = $modules->get($module);

        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:10240',
            ],
        ]);

        $file = fopen(
            $request->file('file')->getRealPath(),
            'rb'
        );

        if ($file === false) {
            return back()->with(
                'error',
                'Unable to read the CSV file.'
            );
        }

        $headers = fgetcsv($file) ?: [];

        $headers = array_map(
            fn ($value) =>
                trim(
                    preg_replace(
                        '/^\xEF\xBB\xBF/',
                        '',
                        (string) $value
                    )
                ),
            $headers
        );

        $invalid = array_diff(
            $headers,
            $definition['columns']
        );

        if ($invalid !== []) {
            fclose($file);

            return back()->with(
                'error',
                'CSV contains unsupported column(s): '
                .implode(', ', $invalid)
                .'. Download the latest template first.'
            );
        }

        $propertyId =
            $request->user()->property_id ?? null;

        $inserted = 0;
        $skipped = 0;

        DB::transaction(function () use (
            $file,
            $headers,
            $definition,
            $propertyId,
            &$inserted,
            &$skipped
        ): void {
            while (($row = fgetcsv($file)) !== false) {
                if (
                    count(
                        array_filter(
                            $row,
                            fn ($value) =>
                                trim((string) $value) !== ''
                        )
                    ) === 0
                ) {
                    $skipped++;
                    continue;
                }

                $payload = [];

                foreach ($headers as $index => $column) {
                    if (! in_array(
                        $column,
                        $definition['columns'],
                        true
                    )) {
                        continue;
                    }

                    $value = $row[$index] ?? null;

                    $payload[$column] =
                        $value === ''
                            ? null
                            : $value;
                }

                if (
                    $propertyId
                    && Schema::hasColumn(
                        $definition['table'],
                        'property_id'
                    )
                ) {
                    $payload['property_id'] =
                        $propertyId;
                }

                if (
                    Schema::hasColumn(
                        $definition['table'],
                        'created_at'
                    )
                ) {
                    $payload['created_at'] = now();
                }

                if (
                    Schema::hasColumn(
                        $definition['table'],
                        'updated_at'
                    )
                ) {
                    $payload['updated_at'] = now();
                }

                if ($payload === []) {
                    $skipped++;
                    continue;
                }

                DB::table(
                    $definition['table']
                )->insert($payload);

                $inserted++;
            }
        });

        fclose($file);

        return back()->with(
            'success',
            $inserted.' row(s) imported. '
            .$skipped.' row(s) skipped.'
        );
    }

    private function query(
        Request $request,
        string $table
    ) {
        $query = DB::table($table);

        if (
            ($propertyId =
                $request->user()->property_id ?? null)
            && Schema::hasColumn(
                $table,
                'property_id'
            )
        ) {
            $query->where(
                'property_id',
                $propertyId
            );
        }

        if (
            Schema::hasColumn(
                $table,
                'deleted_at'
            )
        ) {
            $query->whereNull('deleted_at');
        }

        return $query;
    }

    private function authorise(
        Request $request
    ): void {
        abort_unless(
            in_array(
                strtolower(
                    (string) (
                        $request->user()->role ?? ''
                    )
                ),
                [
                    'super_admin',
                    'admin',
                    'hotel_owner',
                    'general_manager',
                    'branch_manager',
                ],
                true
            ),
            403
        );
    }
}
