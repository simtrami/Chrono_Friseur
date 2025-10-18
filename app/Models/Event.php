<?php

namespace App\Models;

use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Tags\HasTags;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    // Check Spatie/Laravel-tags documentation
    // https://spatie.be/docs/laravel-tags/v4/basic-usage/using-tags
    use HasTags;

    protected $table = 'events';

    protected $fillable = ['name', 'date', 'description', 'import_id', 'import_status'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'datetime:Y-m-d H:i',
        ];
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }
}
