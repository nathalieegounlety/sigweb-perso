{{-- resources/views/exports/paiements_valides.blade.php --}}
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
  <h2>Paiements validés</h2>
  <div class="filtres">
    Édité le {{ now()->format('d/m/Y H:i:s') }}
    @if(!empty($filtres['date_debut'])) — Du {{ \Carbon\Carbon::parse($filtres['date_debut'])->format('d/m/Y') }} @endif
    @if(!empty($filtres['date_fin'])) au {{ \Carbon\Carbon::parse($filtres['date_fin'])->format('d/m/Y') }} @endif
  </div>
  <table>
    <thead><tr>
      <th>#</th>
      <th>Nom & Prénoms</th>
      <th>E-mail</th>
      <th>Role(s)</th>
      <th>Date création</th>
      <th>Date modification</th>
    </tr></thead>
    <tbody>
    @foreach($rows as $i => $r)
      <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $r->name }}</td>
        <td>{{ $r->email }}</td>
        <td>
          @forelse($u->roles as $role)
                        <span class="role">{{ $role->libelle }}</span>
                    @empty
                        <i>Aucun rôle</i>
          @endforelse
        </td>
        <td>{{ \Carbon\Carbon::parse($r->created_at)->format('d/m/Y H:i:s') }}</td> 
        <td>{{ \Carbon\Carbon::parse($r->updated_at)->format('d/m/Y H:i:s') }}</td>
      </tr>
    @endforeach
    </tbody> 
        <tfoot>
            <tr>
                <td colspan="7">
                    Total : {{ $rows->count() }} utilisateur(s)
                    — {{ $rows->where('actif', 1)->count() }} actif(s),
                    {{ $rows->where('actif', 0)->count() }} inactif(s)
                </td>
            </tr>
        </tfoot>
  </table>
</body></html>