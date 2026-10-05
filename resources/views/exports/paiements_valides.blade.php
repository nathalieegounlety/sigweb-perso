<!DOCTYPE html>
<html><head><meta charset="utf-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 9px; }
  h2 { margin: 0 0 4px; }
  .filtres { color: #555; margin-bottom: 8px; }
  table { width: 100%; border-collapse: collapse; }
  th, td { border: 1px solid #999; padding: 3px 4px; }
  th { background: #e5e7eb; }
  .r { text-align: right; }
  tfoot td { font-weight: bold; background: #f3f4f6; }
</style></head>
<body>
  <h2>SIGWEB ERSUMA - Paiements validés</h2>
  <div class="filtres">
    Édité le {{ now()->format('d/m/Y H:i') }}
    @if(!empty($filtres['date_debut'])) — Du {{ \Carbon\Carbon::parse($filtres['date_debut'])->format('d/m/Y') }} @endif
    @if(!empty($filtres['date_fin'])) au {{ \Carbon\Carbon::parse($filtres['date_fin'])->format('d/m/Y') }} @endif
    @if(!empty($filtres['type'])) — Type : {{ $filtres['type'] }} @endif
  </div>
  <table>
    <thead><tr>
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">#</th><th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Nom & Prénoms</th><th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Référence</th><th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Mode</th>
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Montant</th><th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Code transaction</th><th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Type</th><th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Date</th>
    </tr></thead>
    <tbody>
    @foreach($rows as $i => $r)
      <tr>
        <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">{{ $i + 1 }}</td>
        <td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $r->nom }} {{ $r->prenoms }}</td>
        <td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $r->reference }}</td>
        <td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $r->libelle_modepaiement }}</td>
        <td class="r" style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ number_format($r->montant, 0, ',', ' ') }}</td>
        <td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $r->num_transaction }}</td>
        <td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $r->type }}</td>
        <td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ \Carbon\Carbon::parse($r->date)->format('d/m/Y') }}</td>
      </tr>
    @endforeach
    </tbody>
    <tfoot><tr>
      <td colspan="4">Total ({{ $rows->count() }} paiements)</td>
      <td class="r">{{ number_format($total, 0, ',', ' ') }} F CFA</td>
      <td colspan="3"></td>
    </tr></tfoot>
  </table>
</body></html>