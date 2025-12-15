<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BHK extends Model
{
    /** @use HasFactory<\Database\Factories\BHKFactory> */
    use HasFactory;

    protected $table = 'b_h_k_s';
    
    protected $fillable = ['name'];
}
