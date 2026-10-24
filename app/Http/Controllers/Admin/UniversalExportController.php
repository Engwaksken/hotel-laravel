<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UniversalTableExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class UniversalExportController extends Controller
{
    public function __construct(
        private readonly UniversalTableExportService $exports
    ) {
    }

    public function csv(
        Request $request,
        string $module
    ): StreamedResponse {
        abort_unless(
            $request->user(),
            403
        );

        try {
            $definition = $this->exports->module($module);
            $rows = $this->exports->rows(
                $definition,
                $request->user()
            );
        } catch (Throwable $exception) {
            abort(
                404,
                $exception->getMessage()
            );
        }

        $filename = $this->filename(
            $definition['key'],
            'csv'
        );

        return response()->streamDownload(
            function () use (
                $definition,
                $rows
            ): void {
                $handle = fopen(
                    'php://output',
                    'wb'
                );

                if ($handle === false) {
                    return;
                }

                /*
                 * UTF-8 BOM improves Excel compatibility.
                 */
                fwrite(
                    $handle,
                    "\xEF\xBB\xBF"
                );

                fputcsv(
                    $handle,
                    array_values(
                        $definition['columns']
                    )
                );

                foreach ($rows as $row) {
                    $line = [];

                    foreach (
                        array_keys(
                            $definition['columns']
                        ) as $column
                    ) {
                        $line[] = $this->scalar(
                            $row->{$column} ?? null
                        );
                    }

                    fputcsv(
                        $handle,
                        $line
                    );
                }

                fclose($handle);
            },
            $filename,
            [
                'Content-Type' =>
                    'text/csv; charset=UTF-8',
            ]
        );
    }

    public function pdf(
        Request $request,
        string $module
    ) {
        abort_unless(
            $request->user(),
            403
        );

        try {
            $definition = $this->exports->module($module);
            $rows = $this->exports->rows(
                $definition,
                $request->user()
            );
        } catch (Throwable $exception) {
            abort(
                404,
                $exception->getMessage()
            );
        }

        $pdf = Pdf::loadView(
            'exports.universal-table',
            [
                'definition' => $definition,
                'rows' => $rows,
                'generatedBy' =>
                    $request->user()->name
                    ?? 'System User',
            ]
        )->setPaper(
            'a4',
            'landscape'
        );

        return $pdf->download(
            $this->filename(
                $definition['key'],
                'pdf'
            )
        );
    }

    private function filename(
        string $module,
        string $extension
    ): string {
        return sprintf(
            '%s-%s.%s',
            str_replace('_', '-', $module),
            now()->format('Y-m-d-His'),
            $extension
        );
    }

    private function scalar(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (
            is_array($value)
            || is_object($value)
        ) {
            return json_encode(
                $value,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            ) ?: '';
        }

        return (string) $value;
    }
}
