<?php

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Requests\UpdateTagRequest;
use Illuminate\Support\Facades\Route;
use App\Models\Event;
use Spatie\Tags\Tag;

Route::get('/', function () {
    return view('index');
});

Route::get('/tags', function () {
    return Tag::whereType(null)->ordered()->get();
});

Route::post('/tags', function (StoreTagRequest $request) {
    $attributes = $request->validated();
    $tag = Tag::create($attributes);
    return $tag;
});

Route::put('/tags/{id}', function (UpdateTagRequest $request, $id) {
    $attributes = $request->validated();
    $tag = Tag::findOrFail($id);
    $tag->update($attributes);
    $tag->refresh();
    return $tag;
});

Route::delete('/tags/{id}', function ($id) {
    $tag = Tag::findOrFail($id);
    $tag->delete();
    return response()->json(['message' => 'Tag supprimé avec succès.']);
});

Route::get('/events', function () {
    return Event::with('tags')->get();
});

Route::post('/events', function (StoreEventRequest $request) {
    $attributes = $request->validated();
    $event = Event::create($attributes);
    $event->tags()->sync(array_column($attributes['tags'], 'id'));
    $event->load('tags');
    return $event;
});

Route::get('/events/{id}', function ($id) {
    return Event::with('tags')->findOrFail($id);
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
