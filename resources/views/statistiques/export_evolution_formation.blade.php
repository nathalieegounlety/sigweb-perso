<table>
    <thead>
        <tr>
            <th>Année</th>
            <th>Nombre de formations</th> 
        </tr>
    </thead> 
    <tbody> 
        @foreach ($formations_par_annees_statique as $item)
        <tr>
            <td>{{$item->dates}}</td>
            <td>{{$item->nbre_formation}}</td> 
        </tr>
        @endforeach
        @foreach ($formations_par_annees as $item) 
        <tr>
            <td>{{$item->dates}}</td>
            <td>{{$item->nbre_formation}}</td> 
        </tr>
        @endforeach
    </tbody>
</table>