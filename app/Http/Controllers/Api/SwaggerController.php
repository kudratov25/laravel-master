<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

/**
 * Base OpenAPI definition for the entire API.
 * This file is not a real controller — it only holds the top-level Swagger annotations.
 */
#[OA\Info(
    version: '1.0.0',
    title: 'Laravel API Master Template',
    description: 'API documentation for Laravel API Master Template',
)]
#[OA\Server(
    url: '/api',
    description: 'API Server',
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Enter your JWT token. Obtain it from POST /login',
)]
#[OA\Tag(name: 'Auth', description: 'Authentication endpoints')]
#[OA\Tag(name: 'ExamplePost', description: 'Example Post CRUD')]
#[OA\Tag(name: 'Files', description: 'File upload')]
class SwaggerController {}
