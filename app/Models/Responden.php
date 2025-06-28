<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Responden extends Model
{
    use HasFactory;

    protected $table = 'respondens';

    protected $fillable = ['name', 'address', 'contact'];

    public function dataHarian()
    {
        return $this->hasMany(DataHarian::class);
    }

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function pasar()
{
    return $this->belongsTo(Pasar::class);
}


}
