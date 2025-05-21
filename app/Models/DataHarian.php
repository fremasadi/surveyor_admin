<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataHarian extends Model
{
    use HasFactory;

    protected $table = 'data_harian';

    protected $fillable = [
        'user_id',
        'komoditas_id',
        'responden_id',
        'tanggal',
        'status',
        'data_input',
    ];

    /**
     * Get the user associated with this data.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the komoditas associated with this data.
     */
    public function komoditas()
    {
        return $this->belongsTo(Komoditas::class);
    }

    /**
     * Get the responden associated with this data.
     */
    public function responden()
    {
        return $this->belongsTo(Responden::class);
    }
}
