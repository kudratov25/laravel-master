# Laravel API Master Template

A production-ready **Laravel 11 API-only** starter template. Built for building structured, scalable REST APIs fast — with JWT auth, RBAC, file management, translations, audit logging, and a clean response/filter pattern baked in.

---

## Stack

| Layer | Package |
|---|---|
| Framework | Laravel 11, PHP 8.2+ |
| Auth | `php-open-source-saver/jwt-auth` |
| RBAC | `spatie/laravel-permission` v7 |
| Translations | `spatie/laravel-translatable` |
| Database | PostgreSQL (recommended) / MySQL |
| Cache / Queue | Redis |

---

## Getting Started

```bash
# 1. Copy env
cp .env.example .env

# 2. Configure DB, MAIL, and verify JWT_SECRET is set in .env

# 3. Install dependencies
composer install

# 4. Run migrations + seed (creates admin user + roles/permissions)
php artisan migrate --seed

# 5. Link storage
php artisan storage:link

# 6. Serve
php artisan serve
```

**Default admin credentials (from seeder):**
- Email: `test@example.com`
- Password: `password`

---

## Auth Endpoints

| Method | URL | Description |
|---|---|---|
| POST | `/api/login` | Login → returns JWT token |
| POST | `/api/logout` | Logout |
| POST | `/api/refresh` | Refresh token |
| GET | `/api/user` | Get authenticated user |
| PUT | `/api/change-password` | Change password |
| POST | `/api/forgot-password` | Send reset link to email |
| POST | `/api/reset-password` | Reset password with token |

All protected routes require: `Authorization: Bearer {token}`

---

## File Upload Flow

Files are uploaded first, then attached to a model by ID.

```
1. POST /api/v1/files/upload
   Body (multipart): file, field, folder
   Response: { id, url, original_name, mime_type, size }

2. POST /api/v1/admin/example-posts
   Body (JSON): { ..., "cover": 5, "attachments": [6, 7] }
```

---

## Query Parameters (all list endpoints)

| Param | Example | Description |
|---|---|---|
| `per_page` | `?per_page=20` | Items per page (default 10) |
| `per_page=total` | `?per_page=total` | Return all without pagination |
| `sort_by` | `?sort_by=created_at` | Column to sort by |
| `sort_order` | `?sort_order=asc` | `asc` or `desc` |
| `with` | `?with=user,cover` | Eager load relations |
| `search` | `?search=hello` | Full-text search (via `scopeFilter`) |

---

## Translations

Send `Accept-Language` header with every request:

```
Accept-Language: uz   → Uzbek (latin)
Accept-Language: oz   → Uzbek (cyrillic)
Accept-Language: ru   → Russian
Accept-Language: en   → English
```

List endpoints return the translated value for the current locale.
Show endpoints return all translations via `getTranslations()`.

---

## Roles & Permissions

Three default roles seeded:

| Role | Permissions |
|---|---|
| `admin` | All permissions |
| `editor` | viewAny, view, create, update |
| `viewer` | viewAny, view |

Permissions follow the pattern: `{model-slug}.{action}`
Example: `example-post.create`, `example-post.delete`

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── BaseController.php              ← all controllers extend this
│   │   └── Api/V1/
│   │       ├── Auth/AuthController.php
│   │       ├── Admin/                      ← JWT-protected CRUD controllers
│   │       └── Files/FileController.php    ← file upload
│   ├── Middleware/
│   │   ├── JwtAuth.php
│   │   └── SetLocaleFromHeader.php
│   ├── Requests/
│   │   └── Api/
│   │       ├── BaseRequest.php             ← all requests extend this
│   │       └── V1/
│   └── Resources/
│       └── Api/V1/
│           └── {Model}/
│               ├── {Model}sResource.php    ← list (translated value)
│               └── {Model}Resource.php     ← show (all translations)
├── Models/
│   ├── User.php
│   ├── File.php
│   ├── AuditLog.php
│   └── ExamplePost.php                     ← example — delete when building your project
├── Policies/
│   └── ExamplePostPolicy.php               ← example
├── Services/
│   └── FileStoreService.php
└── Traits/
    ├── HasApiResponse.php
    ├── HasApiIndex.php
    ├── Auditable.php
    ├── HasFile.php
    └── HasFiles.php

routes/
├── api.php        ← public auth routes
└── api_v1.php     ← versioned resource routes (v1/admin, v1/site)

database/
├── migrations/
└── seeders/
    ├── DatabaseSeeder.php
    └── RolesAndPermissionsSeeder.php
```

---

## Adding a New Resource

1. **Migration** — `php artisan make:migration create_{name}s_table`
2. **Model** — extend `Model`, add `HasTranslations`, `Auditable`, `HasFiles` as needed
3. **Policy** — follow `ExamplePostPolicy` pattern, register in `AuthServiceProvider`
4. **Requests** — extend `BaseRequest`, one for Store, one for Update
5. **Resources** — two resources: `{Name}sResource` (list) and `{Name}Resource` (show)
6. **Controller** — extend `BaseController`, inject model + `FileStoreService`
7. **Route** — add `Route::apiResource(...)` in `routes/api_v1.php`
8. **Permissions** — add to `RolesAndPermissionsSeeder` and re-seed
