<table>
    <thead>
        <tr>
            <th>Année</th>
            <th>Nombre d'auditeurs</th> 
        </tr>
    </thead> 
    <tbody> 
        @foreach ($auditeur_annees_statique as $item)
        <tr>
            <td>{{$item->dates}}</td>
            <td>{{$item->nbre_auditeur}}</td> 
        </tr>
        @endforeach
        @foreach ($auditeur_annees as $item) 
        <tr>
            <td>{{$item->dates}}</td>
            <td>{{$item->nbre_auditeur}}</td> 
        </tr>
        @endforeach
    </tbody>
</table>