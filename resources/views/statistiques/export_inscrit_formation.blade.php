<table>
    <thead>
        <tr>
            <th colspan="4">LISTE DES INSCRITS PAR FORMATION</th> 
        </tr>
        <tr>
            <th>FORMATION</th> 
                            <th>DATE DEBUT</th> 
                            <th>VISIO</th> 
                            <th>PRESENTIEL</th> 
                            <th>TOTAL INSCRITS</th>
                            <th>TOTAL PARTICIPANTS</th> 
        </tr>
    </thead>  
    <tbody> 
        @foreach ($stat_formation as $item)
        <tr>
            <td>{{$item->code}}</td>
            <td>{{ \Carbon\Carbon::parse($item->date_debut)->format('Y-m-d')  }}</td>  
            <td>{{ $item->nbre_visio }}</td> 
                                  <td>{{ $item->nbre_presentiel  }}</td> 
                                  <td >{{ $item->nbre_presentiel+$item->nbre_visio  }}</td> 
                                  <td >{{ $item->nbre_participant  }}</td> 

        </tr>
        @endforeach 
    </tbody>
</table>