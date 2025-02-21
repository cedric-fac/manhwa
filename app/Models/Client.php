<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
        'tva_rate'
    ];

    protected $casts = [
        'tva_rate' => 'decimal:2'
    ];

    /**
     * Get the readings for the client.
     */
    public function readings()
    {
        return $this->hasMany(Reading::class);
    }

    /**
     * Get the invoices for the client.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
