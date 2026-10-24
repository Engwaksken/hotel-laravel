<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $title }} - Print</title>
<style>
body {
    margin: 20px;
    font-family: Arial, sans-serif;
    color: #222;
}
.actions {
    margin-bottom: 16px;
}
button {
    padding: 9px 14px;
}
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}
th, td {
    padding: 7px;
    border: 1px solid #ddd;
    text-align: left;
}
th {
    background: #f1f5f4;
}
@media print {
    .actions {
        display: none;
    }
    body {
        margin: 0;
    }
}
</style>
</head>
<body>
<div class="actions">
    <button onclick="window.print()">Print</button>
</div>

<h2>{{ $title }}</h2>

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

<script>
window.addEventListener('load', function () {
    setTimeout(function () {
        window.print();
    }, 200);
});
</script>
</body>
</html>
