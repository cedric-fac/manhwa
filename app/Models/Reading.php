<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reading extends Model
{
    protected $fillable = [
        'client_id',
        'value',
        'photo_url',
        'read_at',
        'synced'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'read_at' => 'datetime',
        'synced' => 'boolean'
    ];

    /**
     * Get the client that owns the reading.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the associated invoice if exists.
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
