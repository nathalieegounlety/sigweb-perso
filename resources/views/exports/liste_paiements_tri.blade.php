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
  <h2>SIGWEB ERSUMA - Liste des paiements</h2>
  <div class="filtres">
    Édité le {{ now()->format('d/m/Y H:i') }}
    @if(!empty($filtres['date_debut'])) — Du {{ \Carbon\Carbon::parse($filtres['date_debut'])->format('d/m/Y') }} @endif
    @if(!empty($filtres['date_fin'])) au {{ \Carbon\Carbon::parse($filtres['date_fin'])->format('d/m/Y') }} @endif 
  </div>
  <table>
    <thead><tr>
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">#</th>
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Nom</th>
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Prénoms</th> 
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">E-mail</th>
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Formation</th>
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Référence</th>
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Montant</th> 
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Montant payé</th>
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Montant restant</th>
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Statut</th>
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Mode</th>
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Date</th>
    </tr>
  </thead>
    <tbody>
    @foreach($rows as $i => $r)
      <tr>
        <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">{{ $i + 1 }}</td>
        <td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $r->nom }}</td>
        <td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $r->prenoms }}</td>
        <td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $r->email }}</td>
        <td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $r->reference_paiement }}</td>
        <td class="r" style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ number_format($r->montant, 0, ',', ' ') }}</td>
        <td class="r" style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ number_format($r->montant_total_paye, 0, ',', ' ') }}</td>
        <td class="r" style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ number_format($r->montant_total_restant, 0, ',', ' ') }}</td>
        <td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $r->code_formation }}</td>
        <td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $r->statut_paiement }}</td>
        <td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $r->modepaiement }}</td>
        <td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ \Carbon\Carbon::parse($r->updated_at)->format('d/m/Y') }}</td>
      </tr>
    @endforeach
    </tbody> 
  </table>
</body></html>