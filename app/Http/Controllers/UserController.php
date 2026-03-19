<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
 
    public function index()
    {
        $users = User::all();
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
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'designation' => 'required|string|max:255',
            'other_designation' => 'nullable|string|max:255',
            'password' => 'required|string|min:3',
        ]);
        
        $user = new User();
        $user->name = $request->name;
        $user->pen = $request->pen;
        $user->email = $request->email;
        $user->mobile_number = $request->mobile_number;
        $user->role = $request->role;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos', 'public');
            $user->photo = $photoPath;
        }
        $user->designation = $request->designation;
        $user->other_designation = $request->other_designation;
        $user->password = Hash::make($request->password);
        $user->save();
        
        return redirect()->route("users.index")->with('success', 'User registered successfully.');
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
            $user->name = $request->name;
            $user->pen = $request->pen;
            $user->email = $request->email;
            $user->mobile_number = $request->mobile_number;
            $user->role = $request->role;
            $user->designation = $request->designation;
            $user->other_designation = $request->other_designation;
            
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            
            $user->save();
            
            return redirect()->route("users.index")->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route("users.index")->with('error', 'Failed to update user.');
        }
    }

    public function destroy($id)
    {
        try {
            $userId = decrypt($id);
            $user = User::findOrFail($userId);
            $user->delete();
            
            return redirect()->route("users.index")->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route("users.index")->with('error', 'Failed to delete user.');
        }
    }
}
