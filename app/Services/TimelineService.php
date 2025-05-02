<?php

namespace App\Services;

use App\Models\Timeline;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TimelineService
{
    private const TIMELINE_STORAGE_PATH = 'timelines';

    public function create(array $attributes, ?UploadedFile $picture = null): Timeline
    {
        if ($picture) {
            $attributes['picture'] = $this->storePicture($picture, $attributes['slug']);
        }

        return Timeline::create($attributes);
    }

    private function storePicture(UploadedFile $file, string $slug): string
    {
        return Storage::putFileAs(
            self::TIMELINE_STORAGE_PATH,
            $file,
            "{$slug}.{$file->extension()}"
        );
    }

    public function deletePicture(Timeline $timeline)
    {
        $this->deletePictureIfExists($timeline);
        $timeline->picture = null;
        $timeline->save();
    }

    private function deletePictureIfExists(Timeline $timeline)
    {
        if ($timeline->picture) {
            Storage::delete($timeline->picture);
        }
    }

    public function delete(Timeline $timeline)
    {
        $this->deletePictureIfExists($timeline);
        $timeline->delete();
    }

    public function update(Timeline $timeline, array $attributes, ?UploadedFile $picture = null): Timeline
    {
        if ($picture) {
            $this->deletePictureIfExists($timeline);
            $attributes['picture'] = $this->storePicture($picture, $attributes['slug']);
        } elseif ($timeline->slug !== $attributes['slug']) {
            // Rename the picture file if the slug has changed.
            if ($timeline->picture) {
                $attributes['picture'] = $this->renamePicture($timeline->picture, $attributes['slug']);
            }
        }

        $timeline->update($attributes);

        return $timeline->refresh();
    }

    private function renamePicture(string $picturePath, string $slug): string
    {
        $newPath = self::TIMELINE_STORAGE_PATH.'/'.$slug.'.'.pathinfo($picturePath, PATHINFO_EXTENSION);
        Storage::move($picturePath, $newPath);

        return $newPath;
    }
}
