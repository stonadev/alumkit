<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Actions\UpdateProfileDetails;
use Alumkit\Alumkit\Http\Requests\ProfileDetailsRequest;
use Alumkit\Alumkit\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

class ProfileDetailsController extends Controller
{
    public function update(ProfileDetailsRequest $request): RedirectResponse
    {
        /** @var Profile $profile */
        $profile = $request->user()->profile()->firstOrCreate(); // route group guarantees existence; firstOrCreate avoids a null-profile fatal on direct hits
        (new UpdateProfileDetails)->handle($profile, $request->validated(), $request->file('photo'));

        return redirect()->route('alumkit.profile')->with('status', 'profile-details-updated');
    }
}
