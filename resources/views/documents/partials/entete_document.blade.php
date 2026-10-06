<table class="info">
    <tbody>
        <tr>
            <td class="cellule-titre" style="width: 45%;">{{ $libelleNumero }}</td>
            <td style="width: 20%;">&nbsp;</td>
            <td class="cellule-titre" style="width: 35%;">DATE</td>
        </tr>
        <tr>
            <td class="c"><b>{{ $numero }}</b></td>
            <td>&nbsp;</td>
            <td class="c"><b>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</b></td>
        </tr>
        <tr>
            <td colspan="3" class="cellule-titre"><b>{{ $libelleDestinataire }}</b></td>
        </tr>
        <tr>
            <td colspan="3" style="line-height: 1.5; padding: 6px 4px;">
                <b>{{ $auditeur->prenoms ?? '' }}
                    <span style="text-transform: uppercase;">{{ $auditeur->nom ?? '' }}</span></b><br>
                @if ($auditeur->telephone)
                    Tél : {{ $auditeur->telephone }} -
                @endif
                E-mail : {{ $auditeur->email }}
                @if ($auditeur->ville)
                    <br>{{ $auditeur->ville }},
                @endif
                @if ($auditeur->pays)
                    {{ $auditeur->pays }}
                @endif
            </td>
        </tr>
    </tbody>
</table>
