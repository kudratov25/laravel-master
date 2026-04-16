<?php

namespace App\Http\Controllers\Api\V1\Files;

use App\Http\Controllers\BaseController;
use App\Services\FileStoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileController extends BaseController
{
    public function __construct(protected FileStoreService $fileStoreService) {}

    /**
     * Upload a file and get back a File ID.
     * The client then sends this ID in store/update requests.
     *
     * POST /api/v1/files/upload
     * Body: multipart/form-data
     *   file  → the file
     *   field → "cover" | "attachments" | "avatar" | etc.
     *   folder → optional subfolder name (defaults to "general")
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file'   => 'required|file|max:20480', // 20MB
            'field'  => 'required|string|max:50',
            'folder' => 'nullable|string|max:50',
        ]);

        $file   = $this->fileStoreService->upload(
            $request->file('file'),
            $request->input('folder', 'general'),
            $request->input('field'),
        );

        return $this->successResponse([
            'id'            => $file->id,
            'url'           => $file->url,
            'original_name' => $file->original_name,
            'mime_type'     => $file->mime_type,
            'size'          => $file->size,
        ]);
    }
}
