<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditorias';
    
    protected $fillable = ['usuario', 'acao', 'entidade', 'detalhes'];
    protected $casts = ['detalhes' => 'array'];
}
