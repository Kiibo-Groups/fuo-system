<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Get the post reset redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        $user = auth()->user();

        if ($user) {
            if ($user->role === 'admin' || $user->role === 'owner') {
                return route('admin.dashboard');
            } elseif ($user->role === 'technician') {
                return route('operations.revisions.scan');
            } elseif ($user->role === 'client') {
                return route('store.available');
            }
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/';
    }
}
