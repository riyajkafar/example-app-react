<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $loggedInUser = Auth::user();
        $users = User::query();

        if ($loggedInUser->user_type === 'superadmin') {
            $users->where('user_type', '!=', 'superadmin');
        } elseif ($loggedInUser->user_type === 'admin') {
            $users->whereNotIn('user_type', ['superadmin', 'admin']);
        }

        $users = $users->get();

        return Inertia::render('admin/index', [
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('admin/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'user_type' => 'required|in:superadmin,admin,shopowner,user',
        ]);

        $request->user()->users()->create($validatedData);

        return redirect()->route('admin/index')->with('success', 'User created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);

        return Inertia::render('admin/show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);

        return Inertia::render('admin/edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
              
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'password'=> ['required','min:8'],
            
            'user_type' => 'required|in:superadmin,admin,shopowner,user',
        ]);
        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
        $user->update($validatedData);

        return redirect()->route('admin.index')->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'user deleted successfully']);
    }
}

