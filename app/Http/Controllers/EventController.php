<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchEventRequest;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Services\EventService;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SearchEventRequest $request, EventService $eventService)
    {
        $filters = $request->validated();
        $eventsQuery = $eventService->filterEvents($filters);

        return $eventsQuery->with('tags:id,name,color')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request, EventService $eventService)
    {
        $attributes = $request->validated();

        return $eventService->createEvent($attributes)
            ->loadMissing('tags:id,name,color');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        return $event->loadMissing('tags:id,name,color');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, EventService $eventService, Event $event)
    {
        $attributes = $request->validated();

        return $eventService->updateEvent($event, $attributes)
            ->loadMissing('tags:id,name,color');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json(['message' => 'Événement supprimé avec succès.']);
    }
}
