<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Documentos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        @page {
            margin: 1cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #1f2937;
        }
        .header {
            border-bottom: 2px solid #1e40af;
            padding-bottom: 15px;
            margin-bottom: 20px;
            display: table;
            width: 100%;
        }
        .header-left {
            display: table-cell;
            vertical-align: middle;
        }
        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }
        .company-name {
            font-size: 18pt;
            font-weight: bold;
            color: #1e3a8a;
        }
        .company-subtitle {
            font-size: 9pt;
            color: #6b7280;
        }
        .report-title {
            font-size: 14pt;
            font-weight: 600;
            color: #374151;
        }
        .report-date {
            font-size: 9pt;
            color: #9ca3af;
        }
        .filters {
            background: #f3f4f6;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #e5e7eb;
        }
        .filters-title {
            font-size: 8pt;
            font-weight: bold;
            color: #374151;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .filters-grid {
            display: table;
            width: 100%;
        }
        .filter-item {
            display: table-cell;
            width: 33%;
            font-size: 8pt;
        }
        .filter-label {
            font-weight: bold;
            color: #4b5563;
        }
        .summary {
            margin-bottom: 15px;
            font-size: 10pt;
        }
        .summary-label {
            font-weight: normal;
        }
        .summary-value {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background: #1e40af;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #1e40af;
        }
        td {
            padding: 8px;
            font-size: 9pt;
            border: 1px solid #d1d5db;
        }
        tr:nth-child(even) {
            background: #f9fafb;
        }
        tr:nth-child(odd) {
            background: white;
        }
        .empty-message {
            text-align: center;
            color: #6b7280;
            font-style: italic;
            padding: 20px;
        }
        .status {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-registrado { background: #dbeafe; color: #1e40af; }
        .status-en_proceso { background: #fef3c7; color: #92400e; }
        .status-respondido { background: #d1fae5; color: #065f46; }
        .status-finalizado { background: #dcfce7; color: #166534; }
        .status-rechazado { background: #fee2e2; color: #991b1b; }
        .status-cancelado { background: #f3f4f6; color: #374151; }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            font-size: 8pt;
            color: #9ca3af;
            display: table;
            width: 100%;
        }
        .footer-left {
            display: table-cell;
        }
        .footer-right {
            display: table-cell;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="company-name">TRAMITA YA</div>
            <div class="company-subtitle">Sistema de Gestión Documental</div>
        </div>
        <div class="header-right">
            <div class="report-title">Reporte de Documentos</div>
            <div class="report-date">Fecha: {{ $generatedAt }}</div>
        </div>
    </div>

    @if(count($filters) > 0)
    <div class="filters">
        <div class="filters-title">Filtros aplicados:</div>
        <div class="filters-grid">
            @foreach($filters as $label => $value)
                @if($value)
                <div class="filter-item">
                    <span class="filter-label">{{ $label }}:</span> {{ $value }}
                </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    <div class="summary">
        Total de registros encontrados: <span class="summary-value">{{ $totalRecords }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nro. Trámite</th>
                <th>Expediente</th>
                <th>Asunto</th>
                <th>Tipo</th>
                <th>Oficina</th>
                <th>Estado</th>
                <th>Fecha Reg.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documents as $document)
            <tr>
                <td style="font-weight: 500;">{{ $document->document_number }}</td>
                <td>{{ $document->case_number }}</td>
                <td>{{ $document->subject }}</td>
                <td>{{ $document->type?->name }}</td>
                <td>{{ $document->currentOffice?->name }}</td>
                <td>
                    @php
                        $statusClass = match($document->status) {
                            'registrado' => 'status-registrado',
                            'en_proceso' => 'status-en_proceso',
                            'respondido' => 'status-respondido',
                            'finalizado' => 'status-finalizado',
                            'rechazado' => 'status-rechazado',
                            'cancelado' => 'status-cancelado',
                            default => 'status-registrado',
                        };
                    @endphp
                    <span class="status {{ $statusClass }}">{{ $document->status }}</span>
                </td>
                <td>{{ \Illuminate\Support\Carbon::parse($document->reception_date)->format('d/m/Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="empty-message">No se encontraron registros con los filtros seleccionados.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="footer-left">{{ config('app.name') }} - Reporte generado automáticamente</div>
        <div class="footer-right">Página <span class="pagenum"></span></div>
    </div>
</body>
</html>