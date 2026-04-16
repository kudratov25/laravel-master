<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait HasApiIndex
{
    public function handleApiIndex(Request $request, $query, string $resourceClass, array $extra = []): \Illuminate\Http\JsonResponse
    {
        $model = $query->getModel();
        $lang  = app()->getLocale();

        if ($with = $request->get('with')) {
            $query->with(explode(',', $with));
        }

        if (method_exists($model, 'scopeFilter')) {
            $query->filter($request->all());
        }

        $sortBy    = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');

        if (property_exists($model, 'translatable') && in_array($sortBy, $model->translatable)) {
            $query->orderByRaw("COALESCE({$sortBy}->>'{$lang}', '') {$sortOrder}");
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        if ($request->get('per_page') === 'total') {
            return $this->successResponse($resourceClass::collection($query->get()));
        }

        $paginator = $query->paginate($request->get('per_page', 10))->withQueryString();

        return $this->successIndexResponse(
            $resourceClass::collection($paginator),
            'Operation successful',
            200,
            array_merge([
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ], $extra)
        );
    }
}
