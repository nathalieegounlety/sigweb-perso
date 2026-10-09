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
  <h2>SIGWEB ERSUMA - Liste des historiques des actions du {{ \Carbon\Carbon::parse($date_debut)->format('d/m/Y')  }}  au {{ \Carbon\Carbon::parse($date_fin)->format('d/m/Y')  }} </h2>
  <div class="filtres">
    Édité le {{ now()->format('d/m/Y H:i') }} 
  </div>
  <table>
    <thead><tr>
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">#</th>
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Action</th>
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Description</th>
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Auteur</th>
      <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;">Date</th> 
    </tr>
  </thead>
    <tbody>
    @foreach($liste as $i => $r)
      <tr>
        <td  style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ $i + 1 }}</td>
        <td  style="margin:0; padding:5px; border: 1px solid black; text-align: left;">{{ $r->action }}</td>
        <td  style="margin:0; padding:5px; border: 1px solid black; text-align: left;">{{ $r->description }}</td>         
        <td  style="margin:0; padding:5px; border: 1px solid black; text-align: left;"> 
         {{ $r->getUserCreated?->name ?? '' }}
      </td> 
        <td  style="margin:0; padding:5px; border: 1px solid black; text-align: left;">{{ \Carbon\Carbon::parse($r->created_at)->format('d/m/Y H:i:s') }}</td> 
      </tr>
    @endforeach
    </tbody> 
  </table>
</body></html>