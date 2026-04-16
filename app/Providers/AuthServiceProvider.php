<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\ExamplePost::class => \App\Policies\ExamplePostPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
