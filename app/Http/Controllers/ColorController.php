<?php

namespace App\Http\Controllers;

use App\Models\Color;
use App\Jobs\ProcessImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ColorController extends Controller
{
    // Display a listing of the colors.
    public function index()
    {
        $colors = Color::all();
        return view('colors.index', compact('colors'));
    }

    // Show the form for creating a new color.
    public function create()
    {
        return view('colors.create');
    }

    // Store a newly created color in storage.
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'hex_code'  => 'required|string|max:7',
            'rgb_code'  => 'required|string|max:20',
            'cmyk_code' => 'required|string|max:20',
        ]);

        Color::create($validated);

        return redirect()->route('colors.index')->with('success', 'Color created successfully.');
    }

    // Display the specified color.
    public function show(Color $color)
    {
        return view('colors.show', compact('color'));
    }

    // Show the form for editing the specified color.
    public function edit(Color $color)
    {
        return view('colors.edit', compact('color'));
    }

    // Update the specified color in storage.
    public function update(Request $request, Color $color)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'hex_code'  => 'required|string|max:7',
            'rgb_code'  => 'required|string|max:20',
            'cmyk_code' => 'required|string|max:20',
        ]);

        $color->update($validated);

        return redirect()->route('colors.index')->with('success', 'Color updated successfully.');
    }

    // Remove the specified color from storage.
    public function destroy(Color $color)
    {
        $color->delete();
        return redirect()->route('colors.index')->with('success', 'Color deleted successfully.');
    }

    // Show CSV import form
    public function showImportForm()
    {
        return view('colors.import');
    }

    // Process CSV import by dispatching a job
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);
        Log::info('Import started at: ' . now());
        $path = $request->file('csv_file')->store('public/imports');
        $fullPath = Storage::path($path);
        
        $cacheKey = 'csv_import_' . uniqid();
        Cache::put($cacheKey, $fullPath, now()->addMinutes(30));
        Cache::put('import_progress_' . $cacheKey, 0);
        Cache::put('import_status_' . $cacheKey, 'pending');
        
        ProcessImport::dispatch($cacheKey);
        Log::info('Import job dispatched for cache key: ' . $cacheKey);
        return response()->json([
            'message' => 'Import started',
            'cacheKey' => $cacheKey
        ]);
    }
    
}
