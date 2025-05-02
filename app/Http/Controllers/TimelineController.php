<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTimelineRequest;
use App\Http\Requests\UpdateTimelineRequest;
use App\Http\Resources\TimelineResource;
use App\Models\Timeline;
use App\Services\TimelineService;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class TimelineController extends Controller
{
    use AuthorizesRequests;

    private TimelineService $timelineService;

    /**
     * Constructor with dependency injection
     */
    public function __construct(TimelineService $timelineService)
    {
        $this->timelineService = $timelineService;
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        return TimelineResource::collection($request->user()->timelines);
    }

    public function store(StoreTimelineRequest $request): TimelineResource
    {
        $attributes = $request->validated();

        return new TimelineResource($this->timelineService->create(
            [...$attributes, 'user_id' => $request->user()->id],
            $request->file('picture')
        ));
    }

    public function show(Timeline $timeline): TimelineResource
    {
        $this->authorize('view', $timeline);

        return new TimelineResource($timeline);
    }

    public function getPicture(Timeline $timeline): ResponseFactory|Application|Response
    {
        $this->authorize('view', $timeline);

        return response(Storage::get($timeline->picture),
            headers: ['mime-type' => Storage::mimeType($timeline->picture)]);
    }

    public function update(UpdateTimelineRequest $request, Timeline $timeline): TimelineResource
    {
        return new TimelineResource($this->timelineService
            ->update($timeline, $request->validated(), $request->file('picture'))
        );
    }

    public function deletePicture(Timeline $timeline): JsonResponse
    {
        $this->authorize('update', $timeline);

        $this->timelineService->deletePicture($timeline);

        return response()->json([
            'message' => "L'illustration a bien été supprimée.",
        ]);
    }

    public function destroy(Timeline $timeline): JsonResponse
    {
        $this->authorize('delete', $timeline);

        $this->timelineService->delete($timeline);

        return response()->json([
            'message' => 'La frise a bien été supprimée.',
        ]);
    }
}
