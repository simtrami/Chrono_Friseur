<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportCsvRequest;
use App\Http\Requests\SearchEventRequest;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Models\Import;
use App\Services\CsvImportService;
use App\Services\EventService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    private EventService $eventService;

    private CsvImportService $csvImportService;

    /**
     * Constructor with dependency injection
     */
    public function __construct(EventService $eventService, CsvImportService $csvImportService)
    {
        $this->eventService = $eventService;
        $this->csvImportService = $csvImportService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(SearchEventRequest $request): Collection
    {
        $filters = $request->validated();

        return $this->eventService->filterEvents($filters)
            ->with($this->getTagsRelation())->get();
    }

    /**
     * Get the tags relation with the attributes to include in responses.
     */
    private function getTagsRelation(): string
    {
        // tags:id,name,color
        return 'tags:'.implode(',', [
            'id',
            'name',
            'color',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request): Event
    {
        $attributes = $request->validated();

        return $this->eventService->createEvent($attributes)
            ->loadMissing($this->getTagsRelation());
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event): Event
    {
        return $event
            ->loadMissing($this->getTagsRelation());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, Event $event): Event
    {
        $attributes = $request->validated();

        return $this->eventService->updateEvent($event, $attributes)
            ->loadMissing($this->getTagsRelation());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event): JsonResponse
    {
        $this->eventService->deleteEvent($event);

        return response()->json([
            'message' => "L'événement a bien été supprimé.",
        ]);
    }

    /**
     * Validate CSV file before import
     */
    public function validateCsvImport(ImportCsvRequest $request): JsonResponse
    {
        // If we reach here, validation passed
        $file = $request->file('csv_file');

        // Get file info
        $fileInfo = [
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'lines' => $this->countCsvLines($file),
        ];

        return response()->json([
            'message' => 'Le fichier CSV est valide et prêt à être importé.',
            'file_info' => $fileInfo,
        ]);
    }

    /**
     * Count lines in CSV file
     */
    private function countCsvLines($file): int
    {
        $handle = fopen($file->getRealPath(), 'rb');
        if (! $handle) {
            return 0;
        }

        $lineCount = 0;
        while (fgets($handle) !== false) {
            $lineCount++;
        }
        fclose($handle);

        return $lineCount;
    }

    /**
     * Import events from a CSV file
     */
    public function importCsv(ImportCsvRequest $request): JsonResponse
    {
        try {
            $file = $request->file('csv_file');
            $import = $this->csvImportService->importEvents($file);

            if ($import->status === Import::FAILURE) {
                return response()->json([
                    'message' => "Echec de l'importation. Veuillez vérifier le fichier importé.",
                ], 422);
            }

            // Prepare a response message
            $message = 'Importation terminée avec succès !';

            if ($import->created_events > 0) {
                $message .= " {$import->created_events} événement(s) créé(s).";
            }

            if ($import->created_tags > 0) {
                $message .= " {$import->created_tags} tag(s) créé(s).";
            }

            if ($import->skipped_events > 0) {
                $message .= " {$import->skipped_events} événement(s) ignoré(s) (doublons).";
            }

            return response()->json(['message' => $message]);
        } catch (Exception $e) {
            return response()->json([
                'message' => "Erreur lors de l'importation : ".$e->getMessage(),
            ], 422);
        }
    }
}
