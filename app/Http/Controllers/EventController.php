<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Event::with('tags')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request)
    {
        $attributes = $request->validated();
        $event = Event::create($attributes);
        $event->tags()->sync(array_column($attributes['tags'], 'id'));
        $event->loadMissing('tags:id,name,color');

        return $event;
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
    public function update(UpdateEventRequest $request, Event $event)
    {
        $attributes = $request->validated();
        $event->tags()->sync(array_column($attributes['tags'], 'id'));
        $event->update($attributes);
        $event->loadMissing('tags:id,name,color');

        return $event;
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
