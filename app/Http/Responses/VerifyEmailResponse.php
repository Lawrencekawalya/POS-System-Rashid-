<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;

class VerifyEmailResponse implements VerifyEmailResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();

        return redirect()->to(
            $user->role === 'admin'
            ? route('dashboard', absolute: false) . '?verified=1'
            : route('pos.index', absolute: false) . '?verified=1'
        );
    }
}
