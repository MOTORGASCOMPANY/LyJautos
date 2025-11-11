<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    use HasFactory;

    protected $table="provincia";
    protected $fillable = ['id', 'provincia', 'idDepa'];

    public function Departamento()
    {
        return $this->belongsTo(Departamento::class, 'idDepa');
    }
}
