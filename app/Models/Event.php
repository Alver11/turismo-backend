<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

/**
 * @method static create(array $array)
 * @method static findOrFail($id)
 * @method static where(string $string, true $true)
 */
class Event extends Model
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
        'status',
        'event_date',
        'publication_end_date',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(EventCategory::class, 'event_event_category');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($event) {
            foreach ($event->images as $image) {
                Storage::delete($image->file_path);
                $image->delete();
            }
        });
    }
}
