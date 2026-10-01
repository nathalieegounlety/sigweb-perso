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
      <th>#</th>
      <th>Nom</th>
      <th>Prénoms</th>
      <th>E-mail</th>
      <th>Référence</th>
      <th>Montant</th>
      <th>Formation</th>
      <th>Statut</th>
      <th>Mode</th>
      <th>Date</th>
    </tr>
  </thead>
    <tbody>
    @foreach($rows as $i => $r)
      <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $r->nom }}</td>
        <td>{{ $r->prenoms }}</td>
        <td>{{ $r->email }}</td>
        <td>{{ $r->reference_paiement }}</td>
        <td class="r">{{ number_format($r->montant, 0, ',', ' ') }}</td>
        <td>{{ $r->code_formation }}</td>
        <td>{{ $r->statut_paiement }}</td>
        <td>{{ $r->modepaiement }}</td>
        <td>{{ \Carbon\Carbon::parse($r->updated_at)->format('d/m/Y H:i:s') }}</td>
      </tr>
    @endforeach
    </tbody> 
  </table>
</body></html>