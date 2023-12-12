<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static insert(array[] $data)
 */
class Department extends Model
{
    use HasFactory;

    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }
}
