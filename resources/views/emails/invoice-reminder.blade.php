<x-mail::message>
# Rappel de paiement - Facture {{ $invoice->number }}

Cher(e) {{ $invoice->client->name }},

Ce message est un rappel concernant la facture n° {{ $invoice->number }} qui arrive à échéance {{ $daysUntilDue > 0 ? "dans $daysUntilDue jours" : "depuis " . abs($daysUntilDue) . " jours" }}.

**Détails de la facture:**
- Numéro: {{ $invoice->number }}
- Date d'émission: {{ $invoice->created_at->format('d/m/Y') }}
- Date d'échéance: {{ $invoice->due_date->format('d/m/Y') }}
- Montant dû: {{ number_format($invoice->amount_ttc, 2, ',', ' ') }} FCFA

@if($daysUntilDue < 0)
**Cette facture est en retard de paiement. Merci de régulariser la situation dans les plus brefs délais.**
@else
Nous vous remercions de bien vouloir procéder au paiement avant la date d'échéance.
@endif

<x-mail::button :url="$url">
Voir la facture
</x-mail::button>

Pour toute question concernant cette facture ou son paiement, n'hésitez pas à nous contacter.

Cordialement,<br>
{{ config('billing.company.name') }}

<small>
{{ config('billing.company.address') }}<br>
Tél: {{ config('billing.company.phone') }}<br>
{{ config('billing.company.registration') }}<br>
{{ config('billing.company.tax_id') }}
</small>
</x-mail::message>