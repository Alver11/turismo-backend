<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

/**
 * @method static insert(array[] $data)
 * @method static create(array $array)
 * @method static findOrFail($id)
 */
class TouristPlace extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'district_id',
        'description',
        'lat',
        'lng',
        'user_id',
        'status'
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'attribute_tourist_place')
            ->withPivot('info');
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($touristPlace) {
            foreach ($touristPlace->images as $image) {
                Storage::delete($image->file_path);
                $image->delete();
            }
        });
    }
}
