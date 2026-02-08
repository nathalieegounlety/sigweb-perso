<table>
    <thead>
        <tr>
            <th>Pays</th>
            <th>Nombre d'auditeur</th> 
        </tr>
    </thead> 
    <tbody> 
        @foreach ($auditeurs as $item)
        <tr>
            <td>{{$item->get_pays}}</td>
            <td>{{$item->nbre}}</td> 
        </tr>
        @endforeach 
    </tbody>
</table>