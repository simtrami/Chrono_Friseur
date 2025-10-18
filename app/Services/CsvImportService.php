<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Import;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Spatie\SimpleExcel\SimpleExcelReader;
use Spatie\Tags\Tag;

class CsvImportService
{
    /**
     * Import events from CSV file
     */
    public function importEvents(UploadedFile $file): Import
    {
        $import = Import::create(['name' => $file->getClientOriginalName(), 'status' => Import::FAILURE]);
        $csvData = SimpleExcelReader::create($file, 'csv')->useDelimiter(';')->getRows();

        // Import data in a transaction
        try {
            $stats = $this->processImport($import, $csvData);
        } catch (Exception $e) {
            Event::where('import_id', $import->id)->delete();
            Tag::where('import_id', $import->id)->delete();

            $import->update([
                'status' => Import::FAILURE,
                'created_events' => 0,
                'skipped_events' => 0,
                'created_tags' => 0,
            ]);

            return $import->refresh();
        }

        $import->update([
            'status' => Import::SUCCESS,
            'created_events' => $stats['created_events'],
            'skipped_events' => $stats['skipped_events'],
            'created_tags' => $stats['created_tags'],
        ]);

        return $import->refresh();
    }

    /**
     * Process the import of CSV data
     */
    private function processImport(Import $import, $dataRows): Import
    {
        $dataRows->each(function ($row, $index) use (&$import) {
            $result = $this->processRow($row, $import->id);
            $import->created_events += $result['event_created'] ? 1 : 0;
            $import->skipped_events += $result['event_skipped'] ? 1 : 0;
            $import->created_tags += $result['created_tags'];
        });

        return $import;
    }

    /**
     * Process a single CSV row
     */
    private function processRow(array $row, int $import_id): array
    {
        // Clean and validate data
        $name = trim($row['Nom']);
        $description = trim($row['Description']);
        $dateString = trim($row['Date']);
        $tagsString = trim($row['Tags']);

        $date = Carbon::createFromFormat('Y-m-d', $dateString);

        // Check for duplicate event (same name and date)
        $existingEvent = Event::where('name', $name)
            ->where(DB::raw('DATE(date)'), $date->format('Y-m-d'))
            ->first();

        if ($existingEvent) {
            return [
                'event_created' => false,
                'event_skipped' => true,
                'created_tags' => 0,
            ];
        }

        [$tagIds, $tagsCreated] = $this->processTags($tagsString, $import_id);

        // Create event
        $event = Event::create([
            'name' => $name,
            'description' => $description ?: null,
            'date' => $date,
            'import_id' => $import_id,
        ]);

        // Attach tags
        $event->tags()->sync($tagIds);

        return [
            'event_created' => true,
            'event_skipped' => false,
            'created_tags' => $tagsCreated,
        ];
    }

    private function processTags(string $tagsString, int $import_id): array
    {
        $tagIds = [];
        $tagsCreated = 0;

        $tagNames = array_map('trim', explode(',', $tagsString));

        foreach ($tagNames as $tagName) {
            $tag = $this->getOrCreateTag($tagName, $import_id);
            if ($tag->wasRecentlyCreated) {
                $tagsCreated++;
            }
            $tagIds[] = $tag->id;
        }

        return [$tagIds, $tagsCreated];
    }

    /**
     * Get existing tag or create new one with random color
     */
    private function getOrCreateTag(string $name, int $import_id): Tag
    {
        // Try to find an existing tag
        $existingTag = Tag::findFromStringOfAnyType($name, 'fr')->first();

        if ($existingTag) {
            return $existingTag;
        }

        // Create new tag with random color
        return Tag::create([
            'name' => ['fr' => $name],
            'color' => $this->generateRandomColor(),
            'import_id' => $import_id,
        ]);
    }

    /**
     * Generate a random color for tags
     */
    private function generateRandomColor(): string
    {
        // Colors from https://www.htmlwind.com/colors
        $colors = collect([
            '#ef4444', // red-500
            '#f97316', // orange-500
            '#f59e0b', // amber-500
            '#eab308', // yellow-500
            '#84cc16', // lime-500
            '#22c55e', // green-500
            '#10b981', // emerald-500
            '#14b8a6', // teal-500
            '#06b6d4', // cyan-500
            '#0ea5e9', // sky-500
            '#3b82f6', // blue-500
            '#6366f1', // indigo-500
            '#8b5cf6', // violet-500
            '#a855f7', // purple-500
            '#d946ef', // fuchsia-500
            '#ec4899', // pink-500
            '#f43f5e', // rose-500
        ]);

        return $colors->random();
    }
}
