<?php

namespace App\Http\Controllers\Settings;

use App\Application\UseCases\DeactivateUser\DeactivateUserInputDTO;
use App\Application\UseCases\DeactivateUser\DeactivateUserUseCase;
use App\Application\UseCases\UpdateProfile\UpdateProfileInputDTO;
use App\Application\UseCases\UpdateProfile\UpdateProfileUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private readonly UpdateProfileUseCase $updateProfileUseCase,
        private readonly DeactivateUserUseCase $deactivateUserUseCase,
    ) {}

    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $output = $this->updateProfileUseCase->execute(new UpdateProfileInputDTO(
            userId: $request->user()->id,
            name: $request->validated('name'),
            email: $request->validated('email'),
        ));

        if ($output->emailChanged) {
            $request->user()->email_verified_at = null;
            $request->user()->save();
        }

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $userId = $request->user()->id;

        Auth::logout();

        $this->deactivateUserUseCase->execute(new DeactivateUserInputDTO(userId: $userId));

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
