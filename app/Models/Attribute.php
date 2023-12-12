<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method static insert(array[] $data)
 * @method static get()
 */
class Attribute extends Model
{
    use HasFactory;

    public function touristPlaces(): BelongsToMany
    {
        return $this->belongsToMany(TouristPlace::class, 'attribute_tourist_place')
            ->withPivot('info')
            ->withTimestamps();
    }

}
