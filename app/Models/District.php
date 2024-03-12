<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static insert(array[] $data)
 * @method static where(string $string, int $int)
 */
class District extends Model
{
    use HasFactory;

    public function touristPlaces(): HasMany
    {
        return $this->hasMany(TouristPlace::class);
    }
}
