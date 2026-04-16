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
use OpenApi\Attributes as OA;

class ExamplePostController extends BaseController
{
    public function __construct(
        protected ExamplePost $model,
        protected FileStoreService $fileStoreService,
    ) {}

    #[OA\Get(
        path: '/v1/admin/example-posts',
        summary: 'List all example posts',
        security: [['bearerAuth' => []]],
        tags: ['ExamplePost'],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 10)),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', default: 'id')),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'desc')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'with', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'cover')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of posts'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', $this->model::class);

        $query = $this->model::query()
            ->with(['cover'])
            ->latest();

        return $this->handleApiIndex($request, $query, ExamplePostsResource::class);
    }

    #[OA\Post(
        path: '/v1/admin/example-posts',
        summary: 'Create a new example post',
        security: [['bearerAuth' => []]],
        tags: ['ExamplePost'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'body'],
                properties: [
                    new OA\Property(property: 'title', type: 'object', example: ['uz' => '...', 'oz' => '...', 'ru' => '...']),
                    new OA\Property(property: 'body', type: 'object', example: ['uz' => '...', 'oz' => '...', 'ru' => '...']),
                    new OA\Property(property: 'status', type: 'boolean', example: true),
                    new OA\Property(property: 'cover', type: 'integer', example: 1, description: 'File ID from upload endpoint'),
                    new OA\Property(property: 'attachments', type: 'array', items: new OA\Items(type: 'integer'), example: [2, 3]),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Created successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
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

    #[OA\Get(
        path: '/v1/admin/example-posts/{id}',
        summary: 'Get a single example post',
        security: [['bearerAuth' => []]],
        tags: ['ExamplePost'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Post data with all translations'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $post = $this->model::findOrFail($id);
        $this->authorize('view', $post);

        $post->load(['cover', 'attachments']);

        return $this->successResponse(ExamplePostResource::make($post));
    }

    #[OA\Put(
        path: '/v1/admin/example-posts/{id}',
        summary: 'Update an example post',
        security: [['bearerAuth' => []]],
        tags: ['ExamplePost'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'object'),
                    new OA\Property(property: 'body', type: 'object'),
                    new OA\Property(property: 'status', type: 'boolean'),
                    new OA\Property(property: 'cover', type: 'integer'),
                    new OA\Property(property: 'attachments', type: 'array', items: new OA\Items(type: 'integer')),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Updated successfully'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
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

    #[OA\Delete(
        path: '/v1/admin/example-posts/{id}',
        summary: 'Delete an example post',
        security: [['bearerAuth' => []]],
        tags: ['ExamplePost'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Deleted successfully'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $post = $this->model::findOrFail($id);
        $this->authorize('delete', $post);
        $post->delete();

        return $this->successResponse();
    }
}
