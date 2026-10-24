<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 9px;
    color: #222;
}
h1 {
    margin: 0 0 12px;
    font-size: 16px;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 5px;
    border: 1px solid #d8dde2;
    vertical-align: top;
}
th {
    background: #eef4f3;
    text-align: left;
}
</style>
</head>
<body>
<h1>{{ $title }}</h1>

<table>
<thead>
<tr>
@foreach($columns as $column)
    <th>{{ ucwords(str_replace('_', ' ', $column)) }}</th>
@endforeach
</tr>
</thead>

<tbody>
@foreach($rows as $row)
<tr>
@foreach($columns as $column)
    <td>{{ $row->{$column} ?? '—' }}</td>
@endforeach
</tr>
@endforeach
</tbody>
</table>
</body>
</html>
