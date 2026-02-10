<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('department.manage');
        $query = Department::query();

        if ($request->search) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        $departments = $query->orderBy('name')->paginate(20)->appends($request->all());

        return view('admin.departments.index', [
            'departments' => $departments,
        ]);
    }

    public function create()
    {
        Gate::authorize('department.manage');
        return view('admin.departments.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('department.manage');
        $request->validate([
            'name' => 'required|unique:departments,name',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
        ]);

        Department::create([
            'name' => $request->name,
            'description' => $request->description,
            'icon' => $request->icon,
        ]);

        return redirect()->route('admin.departments.index')->with('success', 'Departemen berhasil dibuat.');
    }

    public function edit($id)
    {
        Gate::authorize('department.manage');
        $department = Department::findOrFail($id);
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('department.manage');
        $department = Department::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:departments,name,' . $department->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
        ]);

        $department->update([
            'name' => $request->name,
            'description' => $request->description,
            'icon' => $request->icon,
        ]);

        return redirect()->route('admin.departments.index')->with('success', 'Departemen berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Gate::authorize('department.manage');
        $department = Department::findOrFail($id);

        if ($department->name === 'GENERAL') {
            return back()->with('error', 'Departemen GENERAL tidak boleh dihapus.');
        }

        $department->delete();

        return redirect()->route('admin.departments.index')->with('success', 'Departemen berhasil dihapus.');
    }
}
