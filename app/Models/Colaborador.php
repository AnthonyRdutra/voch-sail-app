<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Colaborador extends Model
{
    use HasFactory;

    protected $table = 'colaboradores'; 
    protected $fillable = ['nome', 'email', 'cpf', 'unidade_id'];

    public function unidade()
    {
        return $this->belongsTo(Unidade::class);
    }
}
