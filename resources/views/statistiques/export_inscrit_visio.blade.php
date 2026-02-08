
<table>
    <tbody>
        @foreach($liste_inscrits_visio as $item) 
        <tr>
            <td>{{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d') }},{{ $item->email }},{{ $item->prenoms }},{{ $item->nom }}</td>
        </tr> 
        @endforeach
    </tbody>
</table>