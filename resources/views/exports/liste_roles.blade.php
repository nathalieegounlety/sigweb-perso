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
  <h2>SIGWEB ERSUMA - Liste des rôles</h2>
  <div class="filtres">
    Édité le {{ now()->format('d/m/Y H:i') }} 
  </div>
  <table>
    <thead><tr>
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">#</th>
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Libellé</th>
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Statut</th>
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Date création</th>
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Date modification</th>
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Créateur</th> 
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Modificateur</th> 
    </tr>
  </thead>
    <tbody>
    @foreach($liste as $i => $r)
      <tr>
        <td  style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ $i + 1 }}</td>
        <td  style="margin:0; padding:5px; border: 1px solid black; text-align: left;">{{ $r->libelle }}</td>
        <td  style="margin:0; padding:5px; border: 1px solid black; text-align: left;">{{ $r->is_enable ? 'Actif' : 'Inactif' }}</td>
        <td  style="margin:0; padding:5px; border: 1px solid black; text-align: left;">{{ \Carbon\Carbon::parse($r->updated_at)->format('d/m/Y H:i:s') }}</td>
        <td  style="margin:0; padding:5px; border: 1px solid black; text-align: left;">{{ \Carbon\Carbon::parse($r->updated_at)->format('d/m/Y H:i:s') }}</td>
        <td  style="margin:0; padding:5px; border: 1px solid black; text-align: left;">{{ $r->getUserCreated->name }}</td> 
        <td  style="margin:0; padding:5px; border: 1px solid black; text-align: left;">{{ $r->updated_by ? $r->getUserCreated->name : '' }}</td> 
      </tr>
    @endforeach
    </tbody> 
  </table>
</body></html>