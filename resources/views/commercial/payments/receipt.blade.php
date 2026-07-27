@php
    $contract = $payment->contract;
    $reservation = $payment->reservation ?? $contract?->reservation;
    $space = $reservation?->space;

    $methodLabels = [
        'cash' => 'Espèces',
        'cheque' => 'Chèque',
        'bank_transfer' => 'Virement bancaire',
        'tpe' => 'TPE (carte bancaire)',
        'other' => 'Autre',
    ];
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu {{ $payment->receipt_number }} - Hello Desk</title>

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
            min-height: 150mm;
            margin: 0 auto 20px auto;
            padding: 16mm;
            background: #ffffff;
            position: relative;
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

        .amount-banner {
            text-align: center;
            margin: 20px 0;
            padding: 18px;
            border: 2px solid #16a34a;
            border-radius: 12px;
            background: #f0fdf4;
        }

        .amount-banner .amount {
            font-size: 28px;
            font-weight: 800;
            color: #15803d;
        }

        .amount-banner .status {
            margin-top: 4px;
            font-size: 13px;
            font-weight: 700;
            color: #15803d;
            text-transform: uppercase;
            letter-spacing: .5px;
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

        .footnote {
            margin-top: 30px;
            color: #6b7280;
            font-size: 11px;
            text-align: center;
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
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <a href="{{ route('commercial.payments.show', $payment) }}">
            ← Retour à l’échéance
        </a>

        <button onclick="window.print()">
            Imprimer / Enregistrer en PDF
        </button>
    </div>

    <section class="page">
        <header class="header">
            <div>
                <h2 class="brand">HELLO DESK</h2>
                <p class="subtitle">Gestion des espaces de coworking</p>
            </div>

            <div class="ref">
                <strong>Reçu N°</strong><br>
                {{ $payment->receipt_number ?? '—' }}<br>
                Émis le {{ now()->format('d/m/Y') }}
            </div>
        </header>

        <div class="title">
            <h1>Reçu de paiement</h1>
        </div>

        <div class="amount-banner">
            <div class="amount">{{ number_format($payment->amount_ttc_value, 2, ',', ' ') }} DH</div>
            <div class="status">Payé le {{ optional($payment->paid_at)->format('d/m/Y') ?? '—' }}</div>
        </div>

        <div class="grid">
            <div class="card">
                <h3 class="section-title">Client</h3>

                <p class="row"><span class="label">Nom :</span> {{ $payment->client?->full_name ?? 'Non précisé' }}</p>
                <p class="row"><span class="label">Email :</span> {{ $payment->client?->email ?? 'Non précisé' }}</p>
                <p class="row"><span class="label">Téléphone :</span> {{ $payment->client?->phone ?? 'Non précisé' }}</p>
            </div>

            <div class="card">
                <h3 class="section-title">Espace concerné</h3>

                <p class="row"><span class="label">Espace :</span> {{ $space?->name ?? 'Non précisé' }}</p>
                <p class="row"><span class="label">Contrat :</span> {{ $contract?->title ?? 'Non précisé' }}</p>
                <p class="row"><span class="label">Échéance :</span> {{ $payment->duration_label ?? 'Paiement unique' }}</p>
            </div>
        </div>

        <div class="section card">
            <h3 class="section-title">Détails du paiement</h3>

            <div class="grid">
                <p class="row"><span class="label">Date d’échéance :</span> {{ optional($payment->due_date)->format('d/m/Y') ?? 'Non précisée' }}</p>
                <p class="row"><span class="label">Mode de paiement :</span> {{ $methodLabels[$payment->payment_method] ?? 'Non précisé' }}</p>

                @if($payment->payment_method === 'cheque')
                    <p class="row"><span class="label">N° chèque :</span> {{ $payment->cheque_number ?? 'Non précisé' }}</p>
                    <p class="row"><span class="label">Banque :</span> {{ $payment->cheque_bank ?? 'Non précisée' }}</p>
                @elseif($payment->payment_method === 'bank_transfer')
                    <p class="row"><span class="label">Référence virement :</span> {{ $payment->bank_transfer_reference ?? 'Non précisée' }}</p>
                    <p class="row"><span class="label">Banque :</span> {{ $payment->bank_name ?? 'Non précisée' }}</p>
                @elseif($payment->payment_method === 'tpe')
                    <p class="row"><span class="label">Référence TPE :</span> {{ $payment->tpe_transaction_reference ?? 'Non précisée' }}</p>
                @endif

                @if($payment->reference)
                    <p class="row"><span class="label">Référence :</span> {{ $payment->reference }}</p>
                @endif
            </div>
        </div>

        <p class="footnote">
            Ce reçu est généré automatiquement par l’application Hello Desk et confirme la réception du paiement indiqué ci-dessus.
        </p>
    </section>
</body>
</html>
