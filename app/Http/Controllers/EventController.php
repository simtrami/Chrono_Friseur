<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchEventRequest;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SearchEventRequest $request, EventService $eventService): Collection
    {
        $filters = $request->validated();
        $eventsQuery = $eventService->filterEvents($filters);

        return $eventsQuery->with('tags:id,name,color')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request, EventService $eventService): Event
    {
        $attributes = $request->validated();

        return $eventService->createEvent($attributes)
            ->loadMissing('tags:id,name,color');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event): Event
    {
        return $event->loadMissing('tags:id,name,color');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, EventService $eventService, Event $event): Event
    {
        $attributes = $request->validated();

        return $eventService->updateEvent($event, $attributes)
            ->loadMissing('tags:id,name,color');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event, EventService $eventService): JsonResponse
    {
        $eventService->deleteEvent($event);

        return response()->json(['message' => "L'événement a bien été supprimé."]);
    }
}
