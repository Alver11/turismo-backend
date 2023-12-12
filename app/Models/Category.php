<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method static insert(array[] $data)
 * @method static get()
 */
class Category extends Model
{
    use HasFactory;

    protected $table = "categories";

    public function touristPlaces(): BelongsToMany
    {
        return $this->belongsToMany(TouristPlace::class);
    }
}
