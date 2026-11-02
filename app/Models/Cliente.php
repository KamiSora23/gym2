<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';

    protected $primaryKey = 'id_cliente';

    protected $fillable = [
        'dni',
        'nombre',
        'telefono',
        'correo',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
