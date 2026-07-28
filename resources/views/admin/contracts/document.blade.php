@php
    $reservation = $contract->reservation;
    $client = $contract->client;
    $space = $reservation?->space;
    $payments = $contract->payments ?? collect();

    // Was a hardcoded array — now driven by Configuration -> Types de
    // durée de réservation, so a renamed/added duration type displays
    // correctly here too, instead of falling back to the raw code.
    $durationLabels = \App\Models\ReservationDurationType::pluck('name', 'code')->all();

    $totalDue = $payments->sum('amount_due');
    $fallbackAmount = $reservation?->negotiated_price ?? 0;
    $contractAmount = $totalDue > 0 ? $totalDue : $fallbackAmount;
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contrat généré - Hello Desk</title>

    <style>
        @page {
            size: A4;
            margin: 12mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #e5e7eb;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            font-size: 12px;
            line-height: 1.5;
        }

        .toolbar {
            max-width: 210mm;
            margin: 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .toolbar a,
        .toolbar button {
            border: 0;
            border-radius: 10px;
            padding: 10px 14px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
        }

        .toolbar a {
            color: #284625;
            background: #ffffff;
            border: 1px solid #d1d5db;
        }

        .toolbar button {
            color: #ffffff;
            background: #284625;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 20px auto;
            padding: 16mm;
            background: #ffffff;
            page-break-after: always;
            position: relative;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .header {
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 14px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .brand {
            color: #284625;
            font-size: 24px;
            font-weight: 800;
            margin: 0;
        }

        .subtitle {
            margin: 4px 0 0 0;
            color: #6b7280;
            font-size: 12px;
        }

        .ref {
            text-align: right;
            color: #4b5563;
            font-size: 12px;
        }

        .title {
            text-align: center;
            margin: 26px 0 22px 0;
        }

        .title h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .title p {
            margin: 8px 0 0 0;
            color: #6b7280;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .card {
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 14px;
        }

        .section {
            margin-top: 18px;
        }

        .section-title {
            margin: 0 0 10px 0;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            color: #374151;
        }

        .row {
            margin: 6px 0;
        }

        .label {
            font-weight: 700;
            color: #111827;
        }

        .conditions p {
            margin: 0 0 12px 0;
            text-align: justify;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 12px;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 9px;
            text-align: left;
        }

        th {
            background: #f9fafb;
            font-weight: 700;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px;
            margin-top: 45px;
        }

        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #6b7280;
            padding-top: 8px;
            color: #4b5563;
        }

        .page-number {
            position: absolute;
            bottom: 10mm;
            right: 16mm;
            color: #6b7280;
            font-size: 11px;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .toolbar {
                display: none !important;
            }

            .page {
                margin: 0;
                width: auto;
                min-height: auto;
                padding: 0;
                box-shadow: none;
            }

            .page + .page {
                page-break-before: always;
            }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <a href="{{ route('admin.contracts.show', $contract) }}">
            ← Retour au contrat
        </a>

        <button onclick="window.print()">
            Imprimer / Enregistrer en PDF
        </button>
    </div>

    {{-- PAGE 1 --}}
    <section class="page">
        <header class="header">
            <div>
                <h2 class="brand">HELLO DESK</h2>
                <p class="subtitle">Gestion des espaces de coworking</p>
            </div>

            <div class="ref">
                <strong>Référence du contrat</strong><br>
                HD-CONTRAT-{{ str_pad($contract->id, 5, '0', STR_PAD_LEFT) }}<br>
                Généré le {{ now()->format('d/m/Y') }}
            </div>
        </header>

        <div class="title">
            <h1>Contrat de réservation d’un espace de coworking</h1>
            <p>
                Ce document est généré automatiquement à partir des informations enregistrées dans l’application Hello Desk.
            </p>
        </div>

        <div class="grid">
            <div class="card">
                <h3 class="section-title">Informations du client</h3>

                <p class="row">
                    <span class="label">Type de client :</span>
                    {{ $client?->client_type === 'morale' ? 'Personne morale' : 'Personne physique' }}
                </p>

                @if($client?->client_type === 'physique')
                    <p class="row"><span class="label">Nom :</span> {{ $client->last_name ?? 'Non précisé' }}</p>
                    <p class="row"><span class="label">Prénom :</span> {{ $client->first_name ?? 'Non précisé' }}</p>
                    <p class="row"><span class="label">Pièce d’identité :</span> {{ $client->identity_document_type ?? 'Non précisé' }}</p>
                    <p class="row"><span class="label">N° pièce :</span> {{ $client->identity_document_number ?? 'Non précisé' }}</p>
                    <p class="row"><span class="label">Nationalité :</span> {{ $client->nationality ?? 'Non précisée' }}</p>
                @else
                    <p class="row"><span class="label">Raison sociale :</span> {{ $client?->company_name ?? 'Non précisée' }}</p>
                    <p class="row"><span class="label">Forme juridique :</span> {{ $client?->legal_form ?? 'Non précisée' }}</p>
                    <p class="row"><span class="label">ICE :</span> {{ $client?->ice_number ?? 'Non précisé' }}</p>
                    <p class="row"><span class="label">RC :</span> {{ $client?->rc_number ?? 'Non précisé' }}</p>
                    <p class="row"><span class="label">Siège social :</span> {{ $client?->headquarters_address ?? 'Non précisé' }}</p>
                    <p class="row"><span class="label">Représentant légal :</span> {{ $client?->legal_representative_full_name ?? 'Non précisé' }}</p>
                @endif

                <p class="row"><span class="label">Email :</span> {{ $client?->email ?? 'Non précisé' }}</p>
                <p class="row"><span class="label">Téléphone :</span> {{ $client?->phone ?? 'Non précisé' }}</p>
                <p class="row"><span class="label">Adresse :</span> {{ $client?->address ?? 'Non précisée' }}</p>
                <p class="row"><span class="label">Ville :</span> {{ $client?->city ?? 'Non précisée' }}</p>
            </div>

            <div class="card">
                <h3 class="section-title">Informations de l’espace</h3>

                <p class="row"><span class="label">Espace :</span> {{ $space?->name ?? 'Non précisé' }}</p>
                <p class="row"><span class="label">Code :</span> {{ $space?->code ?? 'Non précisé' }}</p>
                <p class="row"><span class="label">Type :</span> {{ $space?->spaceType?->name ?? 'Non précisé' }}</p>
                <p class="row"><span class="label">Site :</span> {{ $reservation?->campus?->name ?? 'Non précisé' }}</p>
                <p class="row"><span class="label">Étage :</span> {{ $reservation?->floor?->name ?? 'Non précisé' }}</p>
            </div>
        </div>

        <div class="section card">
            <h3 class="section-title">Période et conditions financières</h3>

            <div class="grid">
                <p class="row"><span class="label">Date de début :</span> {{ $contract->start_date?->format('d/m/Y') ?? 'Non précisée' }}</p>
                <p class="row"><span class="label">Date de fin :</span> {{ $contract->end_date?->format('d/m/Y') ?? 'Non précisée' }}</p>
                <p class="row"><span class="label">Type de durée :</span> {{ $durationLabels[$reservation?->duration_type] ?? 'Non précisé' }}</p>
                <p class="row"><span class="label">Montant du contrat :</span> {{ number_format($contractAmount, 2, ',', ' ') }} DH</p>
            </div>
        </div>

        <div class="section conditions">
            <h3 class="section-title">Conditions générales</h3>

            <p>
                Le client s’engage à utiliser l’espace réservé dans un cadre professionnel et à respecter le règlement interne de Hello Desk.
            </p>

            <p>
                La période de réservation commence et se termine selon les dates indiquées dans ce contrat. Toute prolongation doit être validée par l’administration ou le personnel commercial de Hello Desk.
            </p>

            <p>
                Le client est responsable du bon usage de l’espace réservé ainsi que des équipements mis à sa disposition pendant la période de réservation.
            </p>

            <p>
                Les échéances de paiement sont suivies dans l’application Hello Desk. Tout retard de paiement peut faire l’objet d’un suivi par le personnel commercial.
            </p>
        </div>

        <div class="page-number">Page 1 / 2</div>
    </section>

    {{-- PAGE 2 --}}
    <section class="page">
        <header class="header">
            <div>
                <h2 class="brand">HELLO DESK</h2>
                <p class="subtitle">Contrat généré</p>
            </div>

            <div class="ref">
                <strong>Référence</strong><br>
                HD-CONTRAT-{{ str_pad($contract->id, 5, '0', STR_PAD_LEFT) }}
            </div>
        </header>

        <div class="section">
            <h3 class="section-title">Échéances de paiement</h3>

            @if($payments->count())
                <table>
                    <thead>
                        <tr>
                            <th>Date d’échéance</th>
                            <th>Montant à payer</th>
                            <th>Statut</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($payments as $payment)
                            <tr>
                                <td>{{ $payment->due_date?->format('d/m/Y') }}</td>
                                <td>{{ number_format($payment->amount_due, 2, ',', ' ') }} DH</td>
                                <td>{{ $payment->real_status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>Aucune échéance de paiement n’est encore enregistrée pour ce contrat.</p>
            @endif
        </div>

        <div class="section conditions">
            <h3 class="section-title">Clauses complémentaires</h3>

            <p>
                Le client reconnaît avoir pris connaissance des informations relatives à l’espace réservé, à la période de réservation et aux conditions financières.
            </p>

            <p>
                Toute modification concernant la durée, le montant ou les conditions de réservation doit être validée par Hello Desk et mise à jour dans l’application.
            </p>

            <p>
                Ce modèle de contrat généré est provisoire. Il pourra être remplacé par le modèle officiel de Hello Desk dès que celui-ci sera fourni.
            </p>
        </div>

        <div class="signatures">
            <div>
                <strong>Signature du client</strong>
                <div class="signature-line">Signature</div>
            </div>

            <div>
                <strong>Représentant Hello Desk</strong>
                <div class="signature-line">Signature et cachet</div>
            </div>
        </div>

        <div class="page-number">Page 2 / 2</div>
    </section>
</body>
</html>