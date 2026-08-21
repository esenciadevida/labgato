<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Alias for EnsureUserIsActive — kept for backward compatibility with route definitions.
 * Both 'active' and 'active.user' route to the same middleware.
 */
class EnsureUserIsActiveUser extends EnsureUserIsActive
{
    //
}
