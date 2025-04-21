<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Event;
use App\Services\TagService;
use Spatie\Tags\Tag;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(TagService $tagService)
    {
        $tagsQuery = $tagService->filterTags([]);

        return $tagsQuery->ordered()->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTagRequest $request, TagService $tagService)
    {
        $attributes = $request->validated();

        return $tagService->createTag($attributes);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag)
    {
        return $tag;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTagRequest $request, TagService $tagService, Tag $tag)
    {
        $attributes = $request->validated();

        return $tagService->updateTag($tag, $attributes);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        // Gather events which had this tag
        $affected_events = Event::withAnyTags($tag)->get();

        $tag->delete();

        // Load tags after removal to get a fresh list
        $affected_events->loadMissing('tags');

        return response()->json([
            'message' => 'Tag supprimé avec succès.',
            'affected_events' => $affected_events,
        ]);
    }
}
