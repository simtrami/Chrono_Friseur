<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Services\TagService;
use Illuminate\Http\JsonResponse;
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
    public function destroy(Tag $tag, TagService $tagService): JsonResponse
    {
        $affectedEvents = $tagService->deleteTag($tag);

        return response()->json([
            'message' => 'Le tag a bien été supprimé.',
            'affected_events' => $affectedEvents,
        ]);
    }
}
