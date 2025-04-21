<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;

class EventService
{
    /**
     * Filter events based on search criteria
     */
    public function filterEvents(array $filters): \Illuminate\Database\Eloquent\Builder
    {
        return Event::query()
            ->when(isset($filters['fulltext']), function ($query) use ($filters) {
                return $query->whereFullText(['name', 'description'], $filters['fulltext']);
            })
            ->when(! empty($filters['with_tags']), function ($query) use ($filters) {
                return $query->whereHas('tags', function (Builder $query) use ($filters) {
                    $query->whereIn('id', $this->extractTagIds($filters['with_tags']));
                });
            })
            ->when(! empty($filters['without_tags']), function ($query) use ($filters) {
                return $query->whereDoesntHave('tags', function (Builder $query) use ($filters) {
                    $query->whereIn('id', $this->extractTagIds($filters['without_tags']));
                });
            })
            ->when(isset($filters['after']), function ($query) use ($filters) {
                return $query->where('date', '>', $filters['after']);
            })
            ->when(isset($filters['before']), function ($query) use ($filters) {
                return $query->where('date', '<', $filters['before']);
            });
    }

    /**
     * Extract tag IDs from an array of tag objects
     */
    private function extractTagIds(array $tags): array
    {
        return array_map(fn ($tag) => $tag['id'], $tags);
    }
}
