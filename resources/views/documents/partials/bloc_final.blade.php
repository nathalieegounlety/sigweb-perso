@php
    $libelleMontant   = $libelleMontant ?? 'Montant en lettres';
    $signatureADroite = $signatureADroite ?? false;
    $signataire       = getSignataireByDate(\Carbon\Carbon::now()->format('Y-m-d'));
@endphp

<div class="bloc-final">
    <p>
        <span style="text-decoration: underline;">{{ $libelleMontant }}</span> :
        <b>{{ $formatter->format($montant) }} francs CFA</b> (XOF).
    </p>

    @if ($signatureADroite)
        {{-- Signature alignée à droite (proforma) --}}
        <div style="margin-top: 30px; text-align: right;">
            <img src="storage/signature/{{ $signataire->signature }}" alt="Signature"
                style="margin: -15px 0 15px; height: 80px;">
            <div style="margin: -20px 0 0;">
                <b><u>{{ $signataire->titre }} {{ $signataire->nom }} {{ $signataire->prenoms }}</u></b><br>
                <small>{{ $signataire->fonction }}</small>
            </div>
        </div>
    @else
        {{-- Signature dans la colonne de droite, centrée sous l'image (reçus) --}}
        <table style="width: 100%; border-collapse: collapse; margin: 0;">
            <tbody>
                <tr>
                    <td style="width: 55%;">&nbsp;</td>
                    <td style="width: 45%; vertical-align: bottom; padding: 0;">
                        <div style="text-align: left; margin: 0; padding: 0;">
                            <img src="storage/signature/{{ $signataire->signature }}" alt="Signature"
                                style="margin: 0 0 -20px -45px; height: 105px; opacity: 0.9;">
                        </div>
                        <div style="margin: -10px 0 0; text-align: center;">
                            <b><u>{{ $signataire->titre }} {{ $signataire->nom }} {{ $signataire->prenoms }}</u></b><br>
                            <small>{{ $signataire->fonction }}</small>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    @endif
</div>
