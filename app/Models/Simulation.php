<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Simulation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'general',
        'rent',
        'buy',
    ];

    protected $casts = [
        'general' => 'array',   
        'rent' => 'array',
        'buy' => 'array',
    ];
}
