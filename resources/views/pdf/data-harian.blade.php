<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Data Pantauan Harga Bahan Pangan Harian</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 10px;
            font-size: 10px;
            line-height: 1.2;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .header h1 {
            font-size: 14px;
            font-weight: bold;
            margin: 5px 0;
            text-transform: uppercase;
        }
        
        .header h2 {
            font-size: 12px;
            font-weight: bold;
            margin: 3px 0;
            text-transform: uppercase;
        }
        
        .date-info {
            text-align: left;
            margin: 10px 0;
            font-size: 10px;
        }
        
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 9px;
        }
        
        .main-table th,
        .main-table td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
            vertical-align: middle;
        }
        
        .main-table th {
            background-color: #e8f4fd;
            font-weight: bold;
            font-size: 8px;
        }
        
        .main-table .komoditas-col {
            text-align: left;
            width: 120px;
        }
        
        .main-table .satuan-col {
            width: 30px;
        }
        
        .main-table .responden-col {
            width: 80px;
        }
        
        .main-table .jmlh-col {
            width: 50px;
            background-color: #fff2cc;
        }
        
        .main-table .rata2-col {
            width: 50px;
            background-color: #fff2cc;
        }
        
        .responden-header {
            background-color: #e8f4fd;
            writing-mode: vertical-rl;
            text-orientation: mixed;
            height: 60px;
            min-width: 50px;
        }
        
        .no-col {
            width: 25px;
        }
        
        .price-cell {
            background-color: #fff2cc;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 20px;
            font-size: 8px;
            text-align: right;
        }
        
        .rotate-text {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REKAPITULASI DATA</h1>
        <h2>PANTAUAN HARGA BAHAN PANGAN HARIAN</h2>
    </div>

    <div class="date-info">
        <strong>TGL/BLN/THN : {{ $period }}</strong>
    </div>

    @if(count($dataHarians) > 0)
    @php
        // Group data by komoditas
        $groupedData = $dataHarians->groupBy('komoditas_id');
        $allResponden = $dataHarians->pluck('responden.name')->unique()->values();
    @endphp
    
    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="2" class="no-col">NO</th>
                <th rowspan="2" class="komoditas-col">KOMODITAS</th>
                <th rowspan="2" class="satuan-col">Sat</th>
                <th colspan="{{ count($allResponden) }}" style="background-color: #e8f4fd;">NAMA</th>
                <th rowspan="2" class="jmlh-col">JMLH</th>
                <th rowspan="2" class="rata2-col">RATA2</th>
            </tr>
            <tr>
                @foreach($allResponden as $responden)
                <th class="responden-header responden-col">
                    <div class="rotate-text">{{ $responden }}</div>
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($groupedData as $komoditasId => $items)
                @php
                    $firstItem = $items->first();
                    $komoditasName = $firstItem->komoditas->name ?? 'Unknown';
                    $satuan = 'Kg'; // Default satuan, bisa disesuaikan dari database
                    
                    // Hitung harga per responden untuk komoditas ini
                    $pricesByResponden = [];
                    $totalPrice = 0;
                    $count = 0;
                    
                    foreach($items as $item) {
                        $respondenName = $item->responden->name ?? 'Unknown';
                        $price = is_numeric($item->data_input) ? (float)$item->data_input : 0;
                        
                        if (!isset($pricesByResponden[$respondenName])) {
                            $pricesByResponden[$respondenName] = [];
                        }
                        
                        if ($price > 0) {
                            $pricesByResponden[$respondenName][] = $price;
                            $totalPrice += $price;
                            $count++;
                        }
                    }
                    
                    // Hitung rata-rata per responden
                    $avgPricesByResponden = [];
                    foreach($pricesByResponden as $responden => $prices) {
                        if (count($prices) > 0) {
                            $avgPricesByResponden[$responden] = array_sum($prices) / count($prices);
                        }
                    }
                    
                    $overallAverage = $count > 0 ? $totalPrice / $count : 0;
                @endphp
                
                <tr>
                    <td class="no-col">{{ $no++ }}</td>
                    <td class="komoditas-col" style="text-align: left;">{{ $komoditasName }}</td>
                    <td class="satuan-col">{{ $satuan }}</td>
                    
                    @foreach($allResponden as $responden)
                        <td class="responden-col price-cell">
                            @if(isset($avgPricesByResponden[$responden]) && $avgPricesByResponden[$responden] > 0)
                                {{ number_format($avgPricesByResponden[$responden], 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                    @endforeach
                    
                    <td class="jmlh-col price-cell">
                        {{ $count > 0 ? number_format($totalPrice, 0, ',', '.') : '-' }}
                    </td>
                    <td class="rata2-col price-cell">
                        {{ $overallAverage > 0 ? number_format($overallAverage, 0, ',', '.') : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    @else
    <div style="text-align: center; padding: 40px; font-style: italic; font-size: 12px;">
        Tidak ada data yang tersedia untuk periode ini.
    </div>
    @endif

    <div class="footer">
        <p>Dicetak pada: {{ $generatedAt }}</p>
    </div>
</body>
</html>