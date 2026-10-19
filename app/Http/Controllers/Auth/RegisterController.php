<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('pages.auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        // Chỉ các trường đã validate (name, email, phone, password); role mặc định customer
        $user = User::create($request->validated());

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', __('auth.registered', ['app' => config('app.name')]));
    }
}
