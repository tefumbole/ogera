<?php

namespace App\Http\Controllers;

/**
 * Placeholder for menus/features that are intentionally disabled on Ogera.
 * Keeps named routes available without shipping Closure routes (route:cache).
 */
class DisabledFeatureController extends Controller
{
    public function __invoke()
    {
        abort(404);
    }
}
