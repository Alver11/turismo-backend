<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

/**
 * @method static insert(array[] $data)
 * @method static get()
 * @method static findOrFail($id)
 */
class Category extends Model
{
    use HasFactory;

    protected $table = "categories";

    public function touristPlaces(): BelongsToMany
    {
        return $this->belongsToMany(TouristPlace::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($category) {
            foreach ($category->images as $image) {
                Storage::delete($image->file_path);
                $image->delete();
            }
        });
    }
}
