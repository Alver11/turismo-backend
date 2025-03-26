<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method static findOrFail($id)
 * @method static create(array $only)
 */
class EventCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_event_category');
    }
}
