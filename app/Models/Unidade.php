<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; 

class Unidade extends Model
{
    use HasFactory; 

    protected $table = 'unidades'; 
    protected $fillable = ['nome_fantasia', 'razao_social', 'cnpj', 'bandeira_id'];

    public function bandeira()
    {
        return $this->belongsTo(Bandeira::class); 
    }

    public function colaboradores()
    {
        return $this->hasMany(Colaborador::class); 
    }
}
