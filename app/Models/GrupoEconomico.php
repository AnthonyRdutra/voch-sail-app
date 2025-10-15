<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GrupoEconomico extends Model
{
    use HasFactory;
    
    protected $table = 'grupo_economico'; 
    protected $fillable = ['nome'];

    public function bandeiras()
    {
        return $this->hasMany(Bandeira::class);
    }
}
