# Laravel API Master Template — Claude Instructions

## Project Type
**Laravel 11 API-only** template. No Blade, no Vite, no frontend. All responses are JSON.

## Stack
- PHP 8.2+, Laravel 11
- PostgreSQL (preferred) / MySQL
- JWT auth: `php-open-source-saver/jwt-auth`
- RBAC: `spatie/laravel-permission` v7
- Translations: `spatie/laravel-translatable`
- Redis for cache/queue

---

## Conventions — Read Before Writing Any Code

### General
- Every new controller extends `BaseController` (`app/Http/Controllers/BaseController.php`)
- Every new form request extends `BaseRequest` (`app/Http/Requests/Api/BaseRequest.php`)
- Every new policy method checks `hasPermissionTo('{model}.{action}')` — add permission to seeder too
- Do not create new base directories without approval
- Do not add packages without approval

### PHP
- Always use curly braces for control structures
- Use PHP 8 constructor property promotion
- No empty `__construct()` with zero parameters
- Always declare explicit return types
- Use `$casts` property on models, not the `casts()` method

### Response Format
Always use `HasApiResponse` methods — never return `response()->json()` directly:
```php
$this->successResponse($data, $message, $status)
$this->successIndexResponse($data, $message, $status, $pagination)
$this->errorResponse($message, $status, $errors)
```
Validation errors are handled automatically by `BaseRequest::failedValidation()`.

### Controllers
- Inject model and services via constructor property promotion
- Use `$this->authorize()` before every action
- No try/catch blocks for `ModelNotFoundException` — handled globally via `bootstrap/app.php`
- Clean single-line methods where possible:
```php
public function show(string $id): JsonResponse
{
    $post = $this->model::findOrFail($id);
    $this->authorize('view', $post);
    return $this->successResponse(ExamplePostResource::make($post->load(['cover'])));
}
```

### Models
- Use `HasTranslations` (Spatie) for multi-language fields — `$translatable = ['name']`
- Use `Auditable` trait for automatic create/update/delete logging
- Use `HasFile` (single file) or `HasFiles` (multiple files) traits for polymorphic file attachments
- Always define `scopeFilter(array $filters)` for search/filter support
- `$casts` must be a property, not a method

### Requests
- One StoreRequest + one UpdateRequest per resource
- For `pmi_id` or unique fields on update, exclude current record: `unique:table,column,' . $this->route('model')`
- Translatable fields validated as arrays: `'name' => 'required|array'`, `'name.uz' => 'required|string'`

### Resources
- Two resources per model:
  - `{Model}sResource` — for index/list: returns `$this->title` (translated for current locale)
  - `{Model}Resource` — for show: returns `$this->getTranslations('title')` (all locales)
- Use `$this->whenLoaded('relation', fn() => ...)` for conditional relation data

### File Uploads
Files are uploaded separately, then attached by ID during store/update.
- Upload: `POST /api/v1/files/upload` → returns `{ id, url, ... }`
- Attach: pass `cover: 5` or `attachments: [6, 7]` in store/update body
- Use `FileStoreService::attachToModel($model, $field, $ids)` in controller
- Files start as unattached (no `fileable_id`), moved to model folder on attach
- Deleting a model auto-deletes its files via `HasFiles::bootHasFiles()`

### Routes
- Public auth routes → `routes/api.php`
- All resource routes → `routes/api_v1.php` under `prefix('v1')`
- Admin routes use `middleware(['jwt.auth', 'localize'])->prefix('admin')`
- Site/public routes use `middleware(['localize'])->prefix('site')`
- Use `Route::apiResource()` for standard CRUD

### RBAC / Policies
- Permissions follow pattern: `{model-slug}.{action}` e.g. `example-post.create`
- Five actions per model: `viewAny`, `view`, `create`, `update`, `delete`
- Register policy in `app/Providers/AuthServiceProvider::$policies`
- Add all permissions to `RolesAndPermissionsSeeder` and assign to appropriate roles

### Translations
- `Accept-Language` header sets locale via `SetLocaleFromHeader` middleware
- Supported locales: `uz`, `oz`, `ru`, `en` (configured in `config/app.php` → `available_locales`)
- Default locale: `uz`, fallback: `ru`

---

## Checklist for Adding a New Resource

- [ ] Migration (`php artisan make:migration create_{name}s_table --no-interaction`)
- [ ] Model with `HasTranslations`, `Auditable`, `HasFiles` as needed + `scopeFilter`
- [ ] Policy (`ExamplePostPolicy` as template) registered in `AuthServiceProvider`
- [ ] `StoreRequest` + `UpdateRequest` extending `BaseRequest`
- [ ] `{Name}sResource` (list) + `{Name}Resource` (show) extending `JsonResource`
- [ ] Controller extending `BaseController` with full CRUD
- [ ] `Route::apiResource()` in `routes/api_v1.php`
- [ ] Permissions added to `RolesAndPermissionsSeeder`

---

## Key Files

| File | Purpose |
|---|---|
| `app/Traits/HasApiResponse.php` | `successResponse`, `errorResponse`, `successIndexResponse` |
| `app/Traits/HasApiIndex.php` | `handleApiIndex()` — pagination, filters, sorting, eager load |
| `app/Traits/Auditable.php` | Auto audit log on create/update/delete |
| `app/Traits/HasFile.php` | `morphOne` file relation |
| `app/Traits/HasFiles.php` | `morphMany` files relation + auto-delete on model delete |
| `app/Http/Controllers/BaseController.php` | Base for all controllers |
| `app/Http/Requests/Api/BaseRequest.php` | JSON validation error format |
| `app/Services/FileStoreService.php` | `upload()` + `attachToModel()` |
| `app/Providers/AuthServiceProvider.php` | Policy map |
| `bootstrap/app.php` | Middleware aliases, JSON exceptions |
| `routes/api.php` | Auth routes (login, logout, refresh, forgot/reset password) |
| `routes/api_v1.php` | All versioned resource routes |
| `database/seeders/RolesAndPermissionsSeeder.php` | Roles + permissions |

---

## Example Reference

`ExamplePost` is a complete working example of the full pattern:
- `app/Models/ExamplePost.php`
- `app/Policies/ExamplePostPolicy.php`
- `app/Http/Requests/Api/V1/ExamplePost/`
- `app/Http/Resources/Api/V1/ExamplePost/`
- `app/Http/Controllers/Api/V1/Admin/ExamplePostController.php`

