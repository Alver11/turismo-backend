<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method static insert(array[] $data)
 * @method static get()
 * @method static create(array $array)
 * @method static findOrFail($id)
 * @method static findById($id)
 */
class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'type_input',
    ];

    public function touristPlaces(): BelongsToMany
    {
        return $this->belongsToMany(TouristPlace::class, 'attribute_tourist_place')
            ->withPivot('info');
    }

}
