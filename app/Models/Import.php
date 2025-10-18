<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    /**
     * Status SUCCESS or FAILURE
     */
    public const SUCCESS = true;

    public const FAILURE = false;

    protected $fillable = [
        'name',
        'status',
        'created_events',
        'skipped_events',
        'created_tags',
    ];

    /**
     * True -> Success
     * False -> Failure
     *
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }
}
