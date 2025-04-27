<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthenticationService
{
    public function redirectToProvider(string $driver, array $scopes = [])
    {
        return Socialite::driver($driver)
            ->scopes($scopes)
            ->redirect();
    }

    public function getUserFromProvider(string $driver)
    {
        return Socialite::driver($driver)->user();
    }

    /**
     * Create or update a user based on GitHub data.
     */
    public function createOrUpdateUserFromGithub($githubUser): User
    {
        return User::updateOrCreate(
            ['github_id' => $githubUser->id],
            [
                'name' => $githubUser->name,
                'email' => $githubUser->email,
                'github_id' => $githubUser->id,
                'github_token' => $githubUser->token,
                'github_refresh_token' => $githubUser->refreshToken,
            ]
        );
    }

    public function logoutUser(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
