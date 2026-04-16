<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Api\V1\ExamplePost\ExamplePostStoreRequest;
use App\Http\Requests\Api\V1\ExamplePost\ExamplePostUpdateRequest;
use App\Http\Resources\Api\V1\ExamplePost\ExamplePostResource;
use App\Http\Resources\Api\V1\ExamplePost\ExamplePostsResource;
use App\Models\ExamplePost;
use App\Services\FileStoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamplePostController extends BaseController
{
    public function __construct(
        protected ExamplePost $model,
        protected FileStoreService $fileStoreService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', $this->model::class);

        $query = $this->model::query()
            ->with(['cover'])
            ->latest();

        return $this->handleApiIndex($request, $query, ExamplePostsResource::class);
    }

    public function store(ExamplePostStoreRequest $request): JsonResponse
    {
        $this->authorize('create', $this->model::class);

        $post = $this->model::create([
            'title'   => $request->title,
            'body'    => $request->body,
            'user_id' => $request->user()->id,
            'status'  => $request->input('status', true),
        ]);

        $this->fileStoreService->attachToModel($post, 'cover', $request->cover);
        $this->fileStoreService->attachToModel($post, 'attachments', $request->attachments);

        return $this->successResponse();
    }

    public function show(string $id): JsonResponse
    {
        $post = $this->model::findOrFail($id);
        $this->authorize('view', $post);

        $post->load(['cover', 'attachments']);

        return $this->successResponse(ExamplePostResource::make($post));
    }

    public function update(ExamplePostUpdateRequest $request, string $id): JsonResponse
    {
        $post = $this->model::findOrFail($id);
        $this->authorize('update', $post);

        $post->update([
            'title'  => $request->title,
            'body'   => $request->body,
            'status' => $request->input('status', $post->status),
        ]);

        $this->fileStoreService->attachToModel($post, 'cover', $request->cover);
        $this->fileStoreService->attachToModel($post, 'attachments', $request->attachments);

        return $this->successResponse();
    }

    public function destroy(string $id): JsonResponse
    {
        $post = $this->model::findOrFail($id);
        $this->authorize('delete', $post);
        $post->delete(); // HasFiles boot deletes all attached files

        return $this->successResponse();
    }
}
