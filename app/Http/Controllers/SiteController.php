<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SiteController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('site.manage');
        $query = Site::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('name', 'LIKE', '%' . $request->search . '%');
            });
        }

        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        $sites = $query->orderBy('name')->paginate(20)->appends($request->all());

        return view('admin.sites.index', compact('sites'));
    }

    public function create()
    {
        Gate::authorize('site.manage');
        return view('admin.sites.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('site.manage');
        $request->validate([
            'code' => 'required|string|max:50|unique:sites,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|in:1',
        ]);

        Site::create([
            'code' => strtoupper(trim($request->code)),
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => (bool) $request->is_active,
        ]);

        return redirect()->route('admin.sites.index')->with('success', 'Site berhasil dibuat.');
    }

    public function edit($id)
    {
        Gate::authorize('site.manage');
        $site = Site::findOrFail($id);
        return view('admin.sites.edit', compact('site'));
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('site.manage');
        $site = Site::findOrFail($id);

        $request->validate([
            'code' => 'required|string|max:50|unique:sites,code,' . $site->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|in:1',
        ]);

        $site->update([
            'code' => strtoupper(trim($request->code)),
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => (bool) $request->is_active,
        ]);

        return redirect()->route('admin.sites.index')->with('success', 'Site berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Gate::authorize('site.manage');
        $site = Site::findOrFail($id);
        $site->delete();
        return redirect()->route('admin.sites.index')->with('success', 'Site berhasil dihapus.');
    }
}

