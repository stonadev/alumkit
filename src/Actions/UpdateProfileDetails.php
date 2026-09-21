<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Actions;

use Alumkit\Alumkit\Models\Profile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

final class UpdateProfileDetails
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Profile $profile, array $data, ?UploadedFile $photo): void
    {
        // Update name/email on the parent User model
        $user = $profile->user;
        $userUpdates = Arr::only($data, ['name', 'email', 'phone']);

        if ($userUpdates) {
            $user->update($userUpdates);
        }

        $data = Arr::except($data, ['photo', 'name', 'email', 'phone']);
        // all-empty platform fields -> null, not missing/empty values
        $data['social_links'] = array_filter(
            $data['social_links'] ?? [],
            fn (mixed $v): bool => $v !== null && $v !== '',
        ) ?: null;
        $data['emergency_contact'] = array_filter(
            $data['emergency_contact'] ?? [],
            fn (mixed $v): bool => $v !== null && $v !== '', // all-empty group -> null
        ) ?: null;
        $data['local_names'] = array_filter(
            $data['local_names'] ?? [],
            fn (mixed $v): bool => $v !== null && $v !== '',
        ) ?: null;

        if ($photo !== null) {
            $path = $photo->store('profile-photos', 'public');
            abort_unless(is_string($path), 500);

            if ($profile->photo_path !== null) {
                Storage::disk('public')->delete($profile->photo_path); // replace semantics
            }
            $data['photo_path'] = $path;
        }
        $profile->update($data);
    }
}
