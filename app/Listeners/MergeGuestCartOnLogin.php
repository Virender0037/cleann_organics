<?php

namespace App\Listeners;

use App\Services\Storefront\CartService;
use Illuminate\Auth\Events\Login;

/**
 * Listens for Laravel's own Login event rather than hooking into
 * AuthenticatedSessionController or RegisteredUserController directly —
 * both already call Auth::login() (via $request->authenticate() or
 * directly), which fires this event either way, so one listener covers
 * both login and registration without touching Breeze's auth flow at all.
 */
class MergeGuestCartOnLogin
{
    public function __construct(private readonly CartService $cart)
    {
    }

    public function handle(Login $event): void
    {
        $this->cart->mergeGuestCartInto($event->user);
    }
}
