<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Client extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'tva_rate'
    ];

    protected $casts = [
        'tva_rate' => 'decimal:2'
    ];

    /**
     * Route notifications for the mail channel.
     */
    public function routeNotificationForMail(): string
    {
        return config('billing.company.email'); // Temporarily use company email for testing
    }

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
