<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1e293b; background: #fff; padding-bottom: 40px; }

  /* HEADER TABLE */
  .header-table { width: 100%; border-collapse: collapse; padding: 20px 28px 16px; border-bottom: 3px solid #1d4ed8; }
  .brand-name { font-size: 19px; font-weight: 700; color: #1d4ed8; letter-spacing: -0.02em; }
  .brand-sub  { font-size: 9.5px; color: #64748b; margin-top: 3px; }
  .header-right { text-align: right; vertical-align: top; }
  .header-label { font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; }
  .header-value { font-size: 10.5px; color: #334155; margin-top: 1px; }

  /* REPORT META */
  .meta { padding: 16px 28px 14px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
  .report-title { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
  .report-desc  { font-size: 9.5px; color: #64748b; line-height: 1.45; }
  .meta-pills   { margin-top: 8px; }
  .pill { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-right: 6px; }
  .pill-cat  { background: #dbeafe; color: #1d4ed8; }
  .pill-rows { background: #dcfce7; color: #166534; }

  /* TABLE */
  .table-wrap { padding: 16px 28px; }
  table.data-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
  table.data-table thead tr { background: #1d4ed8; }
  table.data-table thead th { padding: 8px 10px; text-align: left; color: #fff; font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; }
  table.data-table tbody tr:nth-child(even) { background: #f8fafc; }
  table.data-table tbody tr:nth-child(odd)  { background: #ffffff; }
  table.data-table tbody td { padding: 7px 10px; font-size: 9.5px; color: #334155; border-bottom: 1px solid #e2e8f0; }
  table.data-table tbody tr:last-child td { border-bottom: none; }

  /* FOOTER */
  .footer-table { position: fixed; bottom: 0; left: 0; right: 0; width: 100%; border-collapse: collapse; padding: 8px 28px; border-top: 1px solid #e2e8f0; font-size: 8.5px; color: #94a3b8; background: #fff; }
  .stamp { display: inline-block; border: 1.5px solid #1d4ed8; color: #1d4ed8; padding: 2px 7px; border-radius: 4px; font-size: 8.5px; font-weight: 700; letter-spacing: 0.05em; opacity: 0.6; }
</style>
</head>
<body>

  <!-- Header / Membrete -->
  <table class="header-table">
    <tr>
      <td style="vertical-align: top;">
        <div class="brand-name">&#9881; AutoServicio González</div>
        <div class="brand-sub">Sistema de Gestión de Taller Automotriz</div>
      </td>
      <td class="header-right">
        <div class="header-label">Fecha de emisión</div>
        <div class="header-value">{{ $generatedAt }}</div>
        <div class="header-label" style="margin-top:6px">Sistema</div>
        <div class="header-value">MechanicApp v1.0</div>
      </td>
    </tr>
  </table>

  <!-- Report metadata -->
  <div class="meta">
    <div class="report-title">{{ $report['title'] }}</div>
    <div class="report-desc">{{ $report['description'] ?? '' }}</div>
    <div class="meta-pills">
      <span class="pill pill-cat">{{ $report['category'] }}</span>
      <span class="pill pill-rows">{{ $count }} registros encontrados</span>
    </div>
  </div>

  <!-- Data table -->
  <div class="table-wrap">
    @if(count($data) > 0)
      <table class="data-table">
        <thead>
          <tr>
            @foreach(array_keys((array)$data[0]) as $col)
              <th>{{ strtoupper(str_replace('_', ' ', $col)) }}</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach($data as $row)
            <tr>
              @foreach((array)$row as $value)
                <td>{{ $value ?? '—' }}</td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <p style="color:#94a3b8;margin-top:20px;text-align:center">Sin datos para este reporte.</p>
    @endif
  </div>

  <!-- Footer -->
  <table class="footer-table">
    <tr>
      <td>AutoServicio González &mdash; Documento generado automáticamente para uso administrativo</td>
      <td style="text-align: right;"><span class="stamp">DOCUMENTO INTERNO</span></td>
    </tr>
  </table>

</body>
</html>