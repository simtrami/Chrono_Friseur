<?php

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use Illuminate\Support\Facades\Route;
use App\Models\Event;
use Spatie\Tags\Tag;

Route::get('/', function () {
    return view('index');
});

Route::get('/tags', function () {
    return Tag::whereType(null)->ordered()->get();
});

Route::get('/events', function () {
    return Event::with('tags')->get();
});

Route::get('/events/{id}', function ($id) {
    return Event::with('tags')->findOrFail($id);
});

Route::post('/events',    function (StoreEventRequest $request) {
    $attributes = $request->validated();
    $event = Event::create($attributes);
    $event->tags()->sync(array_column($attributes['tags'], 'id'));
    $event->load('tags');
    return $event;
});

Route::put('/events/{id}', function (UpdateEventRequest $request, $id) {
    $attributes = $request->validated();
    $event = Event::with('tags')->findOrFail($id);
    $event->tags()->sync(array_column($attributes['tags'], 'id'));
    $event->update($attributes);
    $event->refresh();
    return $event;
});

Route::delete('/events/{id}', function ($id) {
    $event = Event::findOrFail($id);
    $event->delete();
    return response()->json(['message' => 'Événement supprimé avec succès.']);
});
