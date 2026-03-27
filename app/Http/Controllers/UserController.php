<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{


    

 
    public function index()
    {
        $users = User::with('profilePhoto')
            ->orderBy('status', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(10);
        return view("admin.users_view", compact("users"));
    }

      public function create()
    {
        return view("admin.user_registration");
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'pen' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'mobile_number' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'user_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'designation' => 'required|string|max:255',
            'other_designation' => 'nullable|string|max:255',
            'password' => 'required|string|min:3',
        ]);
        
        try {
            $user = new User();
            $user->name = $request->name;
            $user->pen = $request->pen;
            $user->email = $request->email;
            $user->mobile_number = $request->mobile_number;
            $user->role = $request->role;
            $user->designation = $request->designation;
            $user->other_designation = $request->other_designation;
            $user->password = Hash::make($request->password);
            $user->save();

            if ($request->hasFile('user_photo')) {
                $file = $request->file('user_photo');

                if ($file->isValid()) {
                    $originalName = $file->getClientOriginalName();
                    $filename = time() . '_' . $originalName;
                    
                    $destinationPath = storage_path('app/public/profile_photos');
                    $file->move($destinationPath, $filename);
                    $path = 'profile_photos/' . $filename;
                    
                    $upload = Upload::create([
                        'petition_id' => null,
                        'category' => Upload::CATEGORY_PROFILE_PHOTO,
                        'original_filename' => $originalName,
                        'file_path' => $path,
                        'uploaded_by' => $user->user_id,
                    ]);
                    
                    $user->photo = $upload->upload_id;
                    $user->save();
                }
            }
            
            return redirect()->route("users.index")->with('success', 'User registered successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to register user: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        try {
            $userId = decrypt($id);
            $user = User::findOrFail($userId);
            return view("admin.edit_users", compact("user"));
        } catch (\Exception $e) {
            return redirect()->route("users.index")->with('error', 'Invalid user ID.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $userId = decrypt($id);
            $user = User::findOrFail($userId);

            $request->validate([
                'name' => 'required|string|max:255',
                'pen' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->user_id . ',user_id',
                'mobile_number' => 'nullable|string|max:255',
                'role' => 'required|string|max:255',
                'user_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'designation' => 'required|string|max:255',
                'other_designation' => 'nullable|string|max:255',
                'status' => 'required|in:Active,Transferred',
                'password' => 'nullable|string|min:3',
            ]);

            $user->name = $request->name;
            $user->pen = $request->pen;
            $user->email = $request->email;
            $user->mobile_number = $request->mobile_number;
            $user->role = $request->role;
            $user->designation = $request->designation;
            $user->other_designation = $request->other_designation;
            $user->status = $request->status;
            
            if ($request->hasFile('user_photo')) {
                $file = $request->file('user_photo');

                if ($file->isValid()) {
                    $originalName = $file->getClientOriginalName();
                    $filename = time() . '_' . $originalName;
                    
                    $destinationPath = storage_path('app/public/profile_photos');
                    $file->move($destinationPath, $filename);
                    $path = 'profile_photos/' . $filename;
                    
                    $upload = Upload::create([
                        'petition_id' => null,
                        'category' => Upload::CATEGORY_PROFILE_PHOTO,
                        'original_filename' => $originalName,
                        'file_path' => $path,
                        'uploaded_by' => $user->user_id,
                    ]);
                    
                    $user->photo = $upload->upload_id;
                }
            }

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            
            $user->save();
            
            return redirect()->route("users.index")->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update user: ' . $e->getMessage())->withInput();
        }
    }
    public function updateStatus(Request $request, $id)
    {
        try {
            $userId = decrypt($id);
            $user = User::findOrFail($userId);
            
            $request->validate([
                'status' => 'required|in:Active,Transferred',
            ]);
            
            $user->status = $request->status;
            $user->save();
            
            return redirect()->route("users.index")->with('success', 'User status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route("users.index")->with('error', 'Failed to update user status.');
        }
    }

    public function destroy($id)
    {
        try {
            $userId = decrypt($id);
            $user = User::findOrFail($userId);
            $user->delete(); // Automatically soft deletes due to SoftDeletes trait
            
            return redirect()->route("users.index")->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route("users.index")->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }
}
