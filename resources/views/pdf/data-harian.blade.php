<!-- Simpan file ini di resources/views/pdf/data-harian.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Data Harian</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin-bottom: 5px;
        }
        .header p {
            margin: 3px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 20px;
            font-size: 10px;
            text-align: right;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            font-style: italic;
        }
        .debug-info {
            font-size: 8px;
            color: #999;
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Data Harian</h1>
        <p><strong>Periode:</strong> {{ $period }}</p>
    </div>

    @if(count($dataHarians) > 0)
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Tanggal</th>
                <th>Komoditas</th>
                <th>Responden</th>
                <th>Status</th>
                <th>Data Input</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataHarians as $index => $data)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($data->tanggal)->format('d-m-Y') }}</td>
                <td>{{ $data->komoditas->name ?? '-' }}</td>
                <td>{{ $data->responden->name ?? '-' }}</td>
                <td>{{ $data->status ? 'Aktif' : 'Tidak Aktif' }}</td>
                <td>{{ $data->data_input }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p>Total data: {{ count($dataHarians) }}</p>
    @else
    <div class="no-data">
        Tidak ada data yang tersedia untuk periode ini.
    </div>
    @endif

    <div class="footer">
        <p>Dicetak pada: {{ $generatedAt }}</p>
    </div>
</body>
</html>