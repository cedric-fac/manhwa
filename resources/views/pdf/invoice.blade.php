<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture {{ $invoice->number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .company-info {
            margin-bottom: 30px;
        }
        .invoice-info {
            margin-bottom: 30px;
        }
        .client-info {
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
        .amounts {
            float: right;
            width: 300px;
            margin-bottom: 30px;
        }
        .amount-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>FACTURE</h1>
            <h2>{{ $invoice->number }}</h2>
        </div>

        <div class="company-info">
            <h3>{{ config('billing.company.name') }}</h3>
            <p>{{ config('billing.company.address') }}</p>
            <p>Tél: {{ config('billing.company.phone') }}</p>
            <p>{{ config('billing.company.registration') }}</p>
            <p>{{ config('billing.company.tax_id') }}</p>
        </div>

        <div class="invoice-info">
            <p><strong>Date d'émission:</strong> {{ $invoice->created_at->format('d/m/Y') }}</p>
            <p><strong>Date d'échéance:</strong> {{ $invoice->due_date->format('d/m/Y') }}</p>
        </div>

        <div class="client-info">
            <h3>Facturé à:</h3>
            <p>{{ $invoice->client->name }}</p>
            <p>{{ $invoice->client->address }}</p>
            <p>Tél: {{ $invoice->client->phone }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Date du relevé</th>
                    <th>Valeur</th>
                    <th>Prix unitaire</th>
                    <th>Montant HT</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Consommation électrique</td>
                    <td>{{ $invoice->reading->read_at->format('d/m/Y') }}</td>
                    <td>{{ $invoice->reading->value }}</td>
                    <td>{{ number_format(config('billing.rate_per_unit'), 2, ',', ' ') }} FCFA</td>
                    <td>{{ number_format($invoice->amount_ht, 2, ',', ' ') }} FCFA</td>
                </tr>
            </tbody>
        </table>

        <div class="amounts">
            <div class="amount-row">
                <span>Montant HT:</span>
                <span>{{ number_format($invoice->amount_ht, 2, ',', ' ') }} FCFA</span>
            </div>
            <div class="amount-row">
                <span>TVA ({{ $invoice->client->tva_rate }}%):</span>
                <span>{{ number_format($invoice->tva, 2, ',', ' ') }} FCFA</span>
            </div>
            <div class="amount-row" style="font-weight: bold;">
                <span>Total TTC:</span>
                <span>{{ number_format($invoice->amount_ttc, 2, ',', ' ') }} FCFA</span>
            </div>
        </div>

        <div class="footer">
            <p>
                {{ config('billing.company.name') }} - {{ config('billing.company.address') }}<br>
                Tél: {{ config('billing.company.phone') }} - Email: {{ config('billing.company.email') }}<br>
                {{ config('billing.company.registration') }} - {{ config('billing.company.tax_id') }}
            </p>
        </div>
    </div>
</body>
</html>