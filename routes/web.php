<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TimelineController;
use App\Models\Timeline;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::controller(AuthenticationController::class)->group(function () {
    Route::get('/auth/github', 'redirectToGithub')->name('auth.github');
    Route::get('/auth/github/callback', 'handleGithubCallback');
    Route::get('/logout', 'logout')->name('logout')->middleware('auth');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
|
| Routes requiring users to be authenticated.
|
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return view('timelines.index');
    })->name('timelines.index');

    Route::get('/{user:username}/{timeline:slug}', function (User $user, Timeline $timeline) {
        Gate::authorize('view', $timeline);

        return view('timelines.show', ['timeline' => $timeline]);
    })->name('timelines.show');

    /*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | RESTful routes for database resources.
    |
    */

    Route::delete('/timelines/{timeline}/picture', [TimelineController::class, 'deletePicture'])
        ->name('timelines.deletePicture');

    Route::get('/storage/timelines/{timeline:slug}.{ext}', [TimelineController::class, 'getPicture'])
        ->name('timelines.getPicture')->where('ext', '[a-z]*');

    /**
     * Registers RESTful routes for the Event and Tag resources,
     * mapping them to the methods in their respective controller class.
     */
    Route::apiResources([
        /*
         * Available routes for timelines and the respective function in the controller:
         *   GET /timelines - index (list all timelines)
         *   GET /timelines/{timeline} - show (view a single timeline)
         *   POST /timelines - store (create a new timeline)
         *   PUT/PATCH /timelines/{timeline} - update (update an existing timeline)
         *   DELETE /timelines/{timeline} - destroy (delete a timeline)
         */
        'timelines' => TimelineController::class,

        /*
         * Available routes for events and the respective function in the controller:
         *   GET /events - index (list all events)
         *   GET /events/{event} - show (view a single event)
         *   POST /events - store (create a new event)
         *   PUT/PATCH /events/{event} - update (update an existing event)
         *   DELETE /events/{event} - destroy (delete an event)
         */
        'events' => EventController::class,

        /*
         * Available routes for tags and the respective function in the controller:
         *   GET /tags - index (list all tags)
         *   GET /tags/{tag} - show (view a single tag)
         *   POST /tags - store (create a new tag)
         *   PUT/PATCH /tags/{tag} - update (update an existing tag)
         *   DELETE /tags/{tag} - destroy (delete a tag)
         */
        'tags' => TagController::class,
    ]);
});
