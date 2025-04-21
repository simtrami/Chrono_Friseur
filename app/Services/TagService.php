<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Tags\Tag;

class TagService
{
    /**
     * Filter tags based on search criteria
     *
     * @param  array  $filters  Optional filtering parameters
     */
    public function filterTags(array $filters): Builder
    {
        return Tag::query()
            ->when(isset($filters['type']), function ($query) use ($filters) {
                return $query->where('type', $filters['type']);
            }, function ($query) { // Defaults to type = null
                return $query->whereNull('type');
            })
            ->when(isset($filters['name']), function ($query) use ($filters) {
                return $query->where('name->fr', 'like', '%'.$filters['name'].'%');
            })
            ->when(isset($filters['color']), function ($query) use ($filters) {
                return $query->where('color', $filters['color']);
            });
    }

    /**
     * Create a new tag
     *
     * @param  array  $attributes  Tag attributes
     * @return Tag The newly created tag
     */
    public function createTag(array $attributes): Tag
    {
        return Tag::create($attributes);
    }

    /**
     * Update an existing tag
     *
     * @param  Tag  $tag  The tag to update
     * @param  array  $attributes  Tag attributes
     * @return Tag The updated tag
     */
    public function updateTag(Tag $tag, array $attributes): Tag
    {
        // Update the tag with attributes
        $tag->update($attributes);

        return $tag->refresh();
    }
}
