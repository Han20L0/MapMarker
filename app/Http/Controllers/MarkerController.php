<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Marker;

class MarkerController extends Controller
{
    public function index()
    {
        $markers = Marker::all();
        return view('map', compact('markers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'quarantine' => 'required',
            'commodity' => 'required',
            'disease' => 'required',
            'information' => 'nullable',
            'color' => 'required|in:red,green',
            'date_found' => 'required|date',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        Marker::create($request->all());
        return redirect()->back();
    }

    public function showByDisease($disease)
    {
        $markers = Marker::where('disease', $disease)->get();
        return response()->json($markers);
    }

    public function destroy(Request $request)
{
    $request->validate([
        'id' => 'required|integer|exists:markers,id',
    ]);

    $marker = Marker::find($request->id);
    if ($marker) {
        $marker->delete();
        return response()->json(['success' => true]);
    }

    return response()->json(['success' => false], 404);
}
}
