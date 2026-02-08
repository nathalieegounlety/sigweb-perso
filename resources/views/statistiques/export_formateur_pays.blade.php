<table>
    <thead>
        <tr>
            <th>Pays</th>
            <th>Nombre de formateurs</th> 
        </tr>
    </thead> 
    <tbody> 
        @foreach ($panelistes as $item)
        <tr>
            <td>{{$item->get_pays}}</td>
            <td>{{$item->nbre}}</td> 
        </tr>
        @endforeach 
    </tbody>
</table>