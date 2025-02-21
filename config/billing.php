<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Billing Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration settings for the billing system.
    |
    */

    // Prix par unité (en FCFA)
    'rate_per_unit' => env('BILLING_RATE_PER_UNIT', 100),

    // Délai de paiement (en jours)
    'payment_term_days' => env('BILLING_PAYMENT_TERM_DAYS', 30),

    // TVA par défaut (%)
    'default_tva_rate' => env('BILLING_DEFAULT_TVA_RATE', 19.25),

    // Format du numéro de facture
    'invoice_number_format' => 'INV-{Y}{m}-{sequence}',

    // Prefix pour le numéro de séquence
    'sequence_prefix' => date('Ym'),

    // Nombre de chiffres pour le numéro de séquence
    'sequence_digits' => 4,

    // Informations de l'entreprise
    'company' => [
        'name' => env('COMPANY_NAME', 'Votre Entreprise'),
        'address' => env('COMPANY_ADDRESS', 'Adresse de l\'entreprise'),
        'phone' => env('COMPANY_PHONE', '+237XXXXXXXXX'),
        'email' => env('COMPANY_EMAIL', 'contact@entreprise.com'),
        'website' => env('COMPANY_WEBSITE', 'www.entreprise.com'),
        'registration' => env('COMPANY_REGISTRATION', 'RC: XXXXX'),
        'tax_id' => env('COMPANY_TAX_ID', 'N° Contrib: XXXXX'),
    ],

    // Configuration des rappels de paiement
    'reminders' => [
        'first' => 7, // Premier rappel X jours avant échéance
        'second' => 3, // Second rappel X jours avant échéance
        'overdue' => 1, // Rappel X jours après échéance
    ],
];