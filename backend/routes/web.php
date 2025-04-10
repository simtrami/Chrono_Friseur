<?php

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use Illuminate\Support\Facades\Route;
use App\Models\Event;

Route::get('/', function () {
    return view('index');
});

Route::get('/events', function () {
    return Event::with('tags')->get();
});

Route::get('/events/{id}', function ($id) {
    return Event::with('tags')->find($id);
});

// Route::get('/events/{id}', function (Event $event) {
//     return $event;
// });

Route::post('/events',    function (StoreEventRequest $request) {
    $attributes = $request->validated();
    return Event::create($attributes);
});

Route::put('/events/{id}', function (UpdateEventRequest $request, $id) {
    $event = Event::findOrFail($id);
    $attributes = $request->validated();
    $event->update($attributes);
    return $event;
});

Route::delete('/events/{id}', function ($id) {
    $event = Event::findOrFail($id);
    $event->delete();
    return response()->json(['message' => 'Event deleted successfully']);
});
