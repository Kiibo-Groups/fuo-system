<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ChecklistTemplate;

class ChecklistController extends Controller
{
    public function index()
    {
        $templates = ChecklistTemplate::orderBy('id', 'desc')->get();
        return view('admin.checklists.index', compact('templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*' => 'required|string|max:255',
        ]);

        ChecklistTemplate::create([
            'title' => $validated['title'],
            'items' => $validated['items'],
            'is_active' => true,
        ]);

        return redirect()->route('admin.checklists.index')->with('success', 'Plantilla de checklist creada.');
    }

    public function update(Request $request, ChecklistTemplate $checklist)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*' => 'required|string|max:255',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $checklist->update($validated);

        return redirect()->route('admin.checklists.index')->with('success', 'Plantilla de checklist actualizada.');
    }

    public function destroy(ChecklistTemplate $checklist)
    {
        $checklist->delete();
        return redirect()->route('admin.checklists.index')->with('success', 'Plantilla de checklist eliminada.');
    }
}
