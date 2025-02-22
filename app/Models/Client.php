<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'tva_rate'
    ];

    protected $casts = [
        'tva_rate' => 'decimal:2'
    ];

    public function readings(): HasMany
    {
        return $this->hasMany(Reading::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function getLatestReading(): ?Reading
    {
        return $this->readings()->latest('read_at')->first();
    }

    public function getUnpaidInvoices()
    {
        return $this->invoices()->whereNull('paid_at')->get();
    }
}
