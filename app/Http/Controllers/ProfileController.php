<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use App\Models\Upload;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display the change password form.
     */
    public function passwordEdit(Request $request): View
    {
        return view('profile.password', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        // Update profile information (excluding photo for separate handling)
        $user->fill($request->safe()->except(['photo']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Handle Profile Photo Upload
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            
            if ($file->isValid() && !empty($file->getRealPath())) {
                $filename = time() . '_profile_' . $user->user_id . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('profile_photos', $filename, 'public');

                // Create entry in uploads table
                $upload = Upload::create([
                    'category' => Upload::CATEGORY_PROFILE_PHOTO,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'uploaded_by' => $user->user_id,
                ]);

                // Delete old photo if exists
                if ($user->profilePhoto) {
                    Storage::disk('public')->delete($user->profilePhoto->file_path);
                    $user->profilePhoto->delete();
                }

                $user->photo = $upload->upload_id;
            }
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
