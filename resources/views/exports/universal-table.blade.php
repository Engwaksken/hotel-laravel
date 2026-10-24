<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <title>{{ $definition['title'] }}</title>

    <style>
        @page {
            margin: 24px;
        }

        body {
            color: #17212b;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
        }

        .header {
            margin-bottom: 14px;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 9px;
        }

        .header h1 {
            margin: 0 0 3px;
            font-size: 17px;
        }

        .header p {
            margin: 0;
            color: #63707a;
            font-size: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #dce3e7;
            padding: 5px 6px;
            vertical-align: top;
            word-break: break-word;
        }

        th {
            background: #edf6f4;
            color: #174c46;
            font-size: 8px;
            text-align: left;
        }

        tbody tr:nth-child(even) td {
            background: #fafcfc;
        }

        .empty {
            padding: 20px;
            color: #6b7782;
            text-align: center;
        }

        .footer {
            margin-top: 10px;
            color: #78848c;
            font-size: 7px;
            text-align: right;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>{{ $definition['title'] }}</h1>

    <p>
        Generated {{ now()->format('d M Y, h:i A') }}
        · By {{ $generatedBy }}
        · {{ $rows->count() }} record(s)
    </p>
</div>

<table>
    <thead>
        <tr>
            @foreach($definition['columns'] as $label)
                <th>{{ $label }}</th>
            @endforeach
        </tr>
    </thead>

    <tbody>
        @forelse($rows as $row)
            <tr>
                @foreach(array_keys($definition['columns']) as $column)
                    @php
                        $value = $row->{$column} ?? null;

                        if (is_bool($value)) {
                            $value = $value ? 'Yes' : 'No';
                        } elseif (is_array($value) || is_object($value)) {
                            $value = json_encode(
                                $value,
                                JSON_UNESCAPED_UNICODE
                                | JSON_UNESCAPED_SLASHES
                            );
                        }
                    @endphp

                    <td>
                        {{ \Illuminate\Support\Str::limit((string) ($value ?? ''), 220) }}
                    </td>
                @endforeach
            </tr>
        @empty
            <tr>
                <td
                    colspan="{{ count($definition['columns']) }}"
                    class="empty"
                >
                    No records found.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    {{ config('app.name', 'Hotel HMS') }}
</div>

</body>
</html>
