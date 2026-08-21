<?php

namespace App\Http\Middleware;

/**
 * Alias for EnsureUserIsActive — kept for backward compatibility with route definitions.
 * Both 'active' and 'active.user' route to the same middleware.
 */
class EnsureUserIsActiveUser extends EnsureUserIsActive
{
    //
}
