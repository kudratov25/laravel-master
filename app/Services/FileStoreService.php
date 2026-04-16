<?php

namespace App\Services;

use App\Models\File as FileModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileStoreService
{
    /**
     * Upload a file to storage and create an unattached File record.
     * The frontend sends this ID when creating/updating a model.
     */
    public function upload(UploadedFile $file, string $folder, string $field = 'file'): FileModel
    {
        $filename  = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path      = Storage::disk('public')->putFileAs("uploads/tmp/{$folder}", $file, $filename);

        return FileModel::create([
            'fileable_type' => null,
            'fileable_id'   => null,
            'field'         => $field,
            'path'          => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'size'          => $file->getSize(),
        ]);
    }

    /**
     * Attach previously uploaded file IDs to a model.
     * Moves files from tmp folder to model folder, deletes removed ones.
     *
     * @param int|int[]|null $fileIds
     */
    public function attachToModel(Model $model, string $field, int|array|null $fileIds): void
    {
        $oldFiles = FileModel::where('fileable_type', get_class($model))
            ->where('fileable_id', $model->id)
            ->where('field', $field)
            ->get();

        if (empty($fileIds)) {
            $oldFiles->each(fn(FileModel $f) => $f->delete());
            return;
        }

        $ids = collect(is_array($fileIds) ? $fileIds : [$fileIds])
            ->filter()->unique()->values()->all();

        // Delete old files that are no longer in the list
        $oldFiles->whereNotIn('id', $ids)->each(fn(FileModel $f) => $f->delete());

        // Attach new files (unattached ones from tmp)
        FileModel::whereIn('id', $ids)
            ->whereNull('fileable_id')
            ->where('field', $field)
            ->each(function (FileModel $file) use ($model) {
                $filename = basename($file->path);
                $folder   = 'uploads/' . Str::kebab(class_basename($model)) . 's/' . $model->id;
                $newPath  = "{$folder}/{$filename}";

                if (Storage::disk('public')->exists($file->path)) {
                    Storage::disk('public')->move($file->path, $newPath);
                }

                $file->update([
                    'fileable_type' => get_class($model),
                    'fileable_id'   => $model->id,
                    'path'          => $newPath,
                ]);
            });
    }
}
