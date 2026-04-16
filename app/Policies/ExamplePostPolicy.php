<?php

namespace App\Policies;

use App\Models\ExamplePost;
use App\Models\User;

class ExamplePostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('example-post.viewAny');
    }

    public function view(User $user, ExamplePost $post): bool
    {
        return $user->hasPermissionTo('example-post.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('example-post.create');
    }

    public function update(User $user, ExamplePost $post): bool
    {
        return $user->hasPermissionTo('example-post.update');
    }

    public function delete(User $user, ExamplePost $post): bool
    {
        return $user->hasPermissionTo('example-post.delete');
    }
}
