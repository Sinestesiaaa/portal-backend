<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['department']);

        // ==========================
        // FILTERING
        // ==========================

        // Filter nama
        if ($request->name) {
            $query->where('name', 'LIKE', '%' . $request->name . '%');
        }

        // Filter role
        if ($request->role_id) {
            $query->where('role_id', $request->role_id);
        }

        // Filter departemen
        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        // ==========================
        // SORTING
        // ==========================
        $allowedSorts = ['name', 'email', 'role_id', 'department_id'];
        $sort = $request->sort ?? 'name';
        $order = $request->order ?? 'asc';

        if (!in_array($sort, $allowedSorts)) $sort = 'name';
        if (!in_array($order, ['asc', 'desc'])) $order = 'asc';

        $query->orderBy($sort, $order);

        // Pagination
        $users = $query->paginate(10)->appends($request->all());

        return view('admin.users.index', [
            'users' => $users,
            'departments' => Department::all(),
        ]);
    }


    public function create()
    {
        $departments = Department::all();
        return view('admin.users.create', compact('departments'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'role_id' => 'required',
            'department_id' => 'nullable',
            'password' => 'required|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'department_id' => $request->department_id,
            'password' => bcrypt($request->password),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dibuat.');
    }


    public function edit($id)
    {
        $user = User::findOrFail($id);
        $departments = Department::all();

        return view('admin.users.edit', compact('user', 'departments'));
    }


    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'role_id' => 'required',
            'department_id' => 'nullable',
        ]);

        $user->update($request->only(['name', 'role_id', 'department_id']));

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }


    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (request()->user()->id === $user->id) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }
}
