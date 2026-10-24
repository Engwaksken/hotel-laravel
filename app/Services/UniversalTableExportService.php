<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class UniversalTableExportService
{
    /**
     * @return array{
     *   key:string,
     *   title:string,
     *   table:string,
     *   columns:array<string,string>
     * }
     */
    public function module(string $module): array
    {
        $config = config("hms_exports.{$module}");

        if (! is_array($config)) {
            throw new InvalidArgumentException(
                'The requested export module is not configured.'
            );
        }

        $table = (string) ($config['table'] ?? '');

        if ($table === '' || ! Schema::hasTable($table)) {
            throw new InvalidArgumentException(
                'The requested export table is unavailable.'
            );
        }

        $configuredColumns = (array) ($config['columns'] ?? []);
        $columns = [];

        foreach ($configuredColumns as $column => $label) {
            if (Schema::hasColumn($table, (string) $column)) {
                $columns[(string) $column] = (string) $label;
            }
        }

        if ($columns === []) {
            throw new InvalidArgumentException(
                'No exportable columns are available for this module.'
            );
        }

        return [
            'key' => $module,
            'title' => (string) ($config['title'] ?? ucfirst($module)),
            'table' => $table,
            'columns' => $columns,
        ];
    }

    public function query(
        array $module,
        ?Authenticatable $user
    ): Builder {
        $table = $module['table'];
        $columns = array_keys($module['columns']);

        $query = DB::table($table)
            ->select($columns);

        /*
        |--------------------------------------------------------------------------
        | Property / Branch Isolation
        |--------------------------------------------------------------------------
        |
        | Any property-scoped table is automatically limited to the user's
        | assigned property unless the user is a Super Admin or Hotel Owner.
        |
        */

        if (
            $user
            && Schema::hasColumn($table, 'property_id')
        ) {
            $role = strtolower(
                (string) ($user->role ?? '')
            );

            $globalRoles = [
                'super_admin',
                'hotel_owner',
            ];

            if (
                ! in_array($role, $globalRoles, true)
                && ! empty($user->property_id)
            ) {
                $query->where(
                    'property_id',
                    (int) $user->property_id
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        $search = trim(
            (string) request()->query('search', '')
        );

        if ($search !== '') {
            $searchable = array_values(
                array_filter(
                    array_keys($module['columns']),
                    static fn (string $column): bool =>
                        ! in_array(
                            $column,
                            [
                                'id',
                                'property_id',
                                'created_at',
                                'updated_at',
                            ],
                            true
                        )
                )
            );

            if ($searchable !== []) {
                $query->where(
                    function (Builder $builder) use (
                        $searchable,
                        $search
                    ): void {
                        foreach ($searchable as $index => $column) {
                            $method = $index === 0
                                ? 'where'
                                : 'orWhere';

                            $builder->{$method}(
                                $column,
                                'like',
                                '%'.$search.'%'
                            );
                        }
                    }
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Simple Status Filter
        |--------------------------------------------------------------------------
        */

        $status = trim(
            (string) request()->query('status', '')
        );

        if (
            $status !== ''
            && Schema::hasColumn($table, 'status')
        ) {
            $query->where('status', $status);
        }

        /*
        |--------------------------------------------------------------------------
        | Date Filter
        |--------------------------------------------------------------------------
        */

        if (Schema::hasColumn($table, 'created_at')) {
            $from = request()->query('from');
            $to = request()->query('to');

            if ($from) {
                $query->whereDate(
                    'created_at',
                    '>=',
                    $from
                );
            }

            if ($to) {
                $query->whereDate(
                    'created_at',
                    '<=',
                    $to
                );
            }
        }

        if (Schema::hasColumn($table, 'id')) {
            $query->orderByDesc('id');
        }

        return $query;
    }

    public function rows(
        array $module,
        ?Authenticatable $user
    ): Collection {
        return $this->query(
            $module,
            $user
        )->get();
    }
}
