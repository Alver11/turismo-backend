<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @method static findOrFail($id)
 */
class TypeService extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];
}
