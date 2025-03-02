<?php

use Illuminate\Support\Facades\Route;
use App\Models\Event;

Route::get('/', function () {
    return view('index');
});

Route::get('/joli', function () {
    return view('joli');
});

Route::get('/events', function () {
    $events = Event::all();
    return $events;
});

Route::get('/events/{id}', function ($id) {
    $event = Event::find($id);
    return $event;
});

// Route::get('/events/{id}', function (Event $event) {
//     return $event;
// });

Route::post('/events', function () {
    $event = Event::create(request()->all());
    return $event;
});

Route::put('/events/{id}', function ($id) {
    Event::find($id)->update(request()->all()); // boolean
    return Event::find($id);
});
