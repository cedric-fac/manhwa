<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'client_id',
        'reading_id',
        'amount_ht',
        'tva',
        'amount_ttc',
        'status',
        'due_date'
    ];

    protected $casts = [
        'amount_ht' => 'decimal:2',
        'tva' => 'decimal:2',
        'amount_ttc' => 'decimal:2',
        'due_date' => 'date',
        'status' => InvoiceStatus::class
    ];

    /**
     * Get the client that owns the invoice.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the reading associated with this invoice.
     */
    public function reading()
    {
        return $this->belongsTo(Reading::class);
    }

    /**
     * Calculate invoice amounts based on reading and client TVA rate.
     */
    public static function calculateAmounts(Reading $reading): array
    {
        $amount_ht = $reading->value * config('billing.rate_per_unit', 100);
        $tva = $amount_ht * ($reading->client->tva_rate / 100);
        $amount_ttc = $amount_ht + $tva;

        return [
            'amount_ht' => $amount_ht,
            'tva' => $tva,
            'amount_ttc' => $amount_ttc
        ];
    }
}
