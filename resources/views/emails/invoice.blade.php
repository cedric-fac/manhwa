<x-mail::message>
# Nouvelle Facture {{ $invoice->number }}

Cher(e) {{ $invoice->client->name }},

Veuillez trouver ci-joint votre facture n° {{ $invoice->number }} d'un montant de {{ number_format($invoice->amount_ttc, 2, ',', ' ') }} FCFA.

**Détails de la facture:**
- Numéro: {{ $invoice->number }}
- Date d'émission: {{ $invoice->created_at->format('d/m/Y') }}
- Date d'échéance: {{ $invoice->due_date->format('d/m/Y') }}
- Montant HT: {{ number_format($invoice->amount_ht, 2, ',', ' ') }} FCFA
- TVA ({{ $invoice->client->tva_rate }}%): {{ number_format($invoice->tva, 2, ',', ' ') }} FCFA
- Montant TTC: {{ number_format($invoice->amount_ttc, 2, ',', ' ') }} FCFA

<x-mail::button :url="$url">
Voir la facture
</x-mail::button>

Pour toute question concernant cette facture, n'hésitez pas à nous contacter.

Cordialement,<br>
{{ config('billing.company.name') }}

<small>
{{ config('billing.company.address') }}<br>
Tél: {{ config('billing.company.phone') }}<br>
{{ config('billing.company.registration') }}<br>
{{ config('billing.company.tax_id') }}
</small>
</x-mail::message>