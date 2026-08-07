<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Upload;
use App\Models\SeatUser;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(): View
    {
        $users = User::with('profilePhoto')
            ->orderBy('status', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(10);
            
        return view("admin.users_view", compact("users"));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        return view("admin.user_registration");
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        return DB::transaction(function () use ($request) {
            try {
                $user = User::create([
                    'name' => $request->name,
                    'pen' => $request->pen,
                    'email' => $request->email,
                    'mobile_number' => $request->mobile_number,
                    'role' => $request->role,
                    'designation' => $request->designation,
                    'other_designation' => $request->other_designation,
                    'password' => Hash::make($request->password),
                ]);

                if ($request->hasFile('user_photo')) {
                    $this->handleProfilePhoto($user, $request->file('user_photo'));
                }
                
                return redirect()->route("users.index")->with('success', 'User registered successfully.');
            } catch (\Exception $e) {
                return back()->with('error', 'Failed to register user: ' . $e->getMessage())->withInput();
            }
        });
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(string $id): View|RedirectResponse
    {
        try {
            $user = User::findOrFail(decrypt($id));
            return view("admin.edit_users", compact("user"));
        } catch (\Exception $e) {
            return redirect()->route("users.index")->with('error', 'Invalid user selection.');
        }
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, string $id): RedirectResponse
    {
        return DB::transaction(function () use ($request, $id) {
            try {
                $user = User::findOrFail(decrypt($id));

                $userData = $request->only([
                    'name', 'pen', 'email', 'mobile_number', 'role', 
                    'designation', 'other_designation', 'status'
                ]);

                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($request->password);
                }

                $user->update($userData);

                if ($request->has('remove_photo') && $request->remove_photo == '1') {
                    if ($user->profilePhoto) {
                        Storage::disk('public')->delete($user->profilePhoto->file_path);
                        $user->profilePhoto->delete();
                        $user->update(['photo' => null]);
                    }
                } elseif ($request->hasFile('user_photo')) {
                    $this->handleProfilePhoto($user, $request->file('user_photo'));
                }

                if ($request->status === 'Transferred') {
                    $this->revokeActiveSeats($user->user_id);
                }
                
                return redirect()->route("users.index")->with('success', 'User updated successfully.');
            } catch (\Exception $e) {
                return back()->with('error', 'Failed to update user: ' . $e->getMessage())->withInput();
            }
        });
    }

    /**
     * Update the status of specified user.
     */
    public function updateStatus(Request $request, string $id): RedirectResponse
    {
        try {
            $request->validate(['status' => 'required|in:Active,Transferred']);
            
            $user = User::findOrFail(decrypt($id));
            $user->update(['status' => $request->status]);

            if ($request->status === 'Transferred') {
                $this->revokeActiveSeats($user->user_id);
            }
            
            return redirect()->route("users.index")->with('success', 'User status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route("users.index")->with('error', 'Failed to update user status.');
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        return DB::transaction(function () use ($id) {
            try {
                $user = User::findOrFail(decrypt($id));
                $userId = $user->user_id;
                
                $user->delete();
                $this->revokeActiveSeats($userId);
                
                return redirect()->route("users.index")->with('success', 'User deleted successfully.');
            } catch (\Exception $e) {
                return redirect()->route("users.index")->with('error', 'Failed to delete user.');
            }
        });
    }

    /**
     * Helper: Handle profile photo upload and cleanup.
     */
    private function handleProfilePhoto(User $user, $file): void
    {
        if ($file->isValid()) {
            $filename = time() . "_profile_{$user->user_id}." . $file->getClientOriginalExtension();
            $path = $file->storeAs('profile_photos', $filename, 'public');
            
            $upload = Upload::create([
                'category' => Upload::CATEGORY_PROFILE_PHOTO,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'uploaded_by' => $user->user_id,
            ]);
            
            if ($user->profilePhoto) {
                Storage::disk('public')->delete($user->profilePhoto->file_path);
                $user->profilePhoto->delete();
            }
            
            $user->update(['photo' => $upload->upload_id]);
        }
    }

    /**
     * Helper: Revoke all active seat assignments for a user.
     */
    private function revokeActiveSeats(int $userId): void
    {
        SeatUser::where('user_id', $userId)
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'revoked_at' => now(),
            ]);
    }

    /**
     * AJAX endpoint to check unique values.
     */
    public function checkUnique(Request $request): JsonResponse
    {
        $request->validate([
            'field' => 'required|string|in:email,pen',
            'value' => 'required|string|max:255',
        ]);

        $exists = User::where($request->field, $request->value)->exists();
        return response()->json(['exists' => $exists]);
    }
}
