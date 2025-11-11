<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candado extends Model
{
    use HasFactory;
    protected $table = 'candado';
    protected $fillable = ['nombre', 'valor'];
    public $timestamps = true;
}
