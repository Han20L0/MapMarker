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

//     public function store(Request $request)
// {
//     $request->validate([
//         'quarantine' => 'required',
//         'commodity' => 'required',
//         'disease' => 'required',
//         'information' => 'nullable',
//         'color' => 'required|in:red,green',
//         'date_found' => 'required|date',
//         'latitude' => 'required|numeric',
//         'longitude' => 'required|numeric',
//         'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi foto
//     ]);

//     // Simpan foto di folder default
//     if ($request->hasFile('photo')) {
//         $photoPath = $request->file('photo')->store('photos', 'public'); // Simpan di folder storage/app/public/photos
//     }

//     // Buat marker dengan path foto
//     Marker::create(array_merge($request->all(), ['photo_path' => $photoPath]));

//     return redirect()->back()->with('success', 'Marker berhasil ditambahkan!');
// }


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
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi foto
    ]);

    // Inisialisasi $photoPath dengan null
    $photoPath = null;

    // Simpan foto di folder default jika ada
    if ($request->hasFile('photo')) {
        $photoPath = $request->file('photo')->store('photos', 'public'); // Simpan di folder storage/app/public/photos
    }

    // Buat marker dengan path foto
    Marker::create(array_merge($request->all(), ['photo_path' => $photoPath]));

    // return response()->json(['success' => true, 'marker' => $request->all()]); // Kembalikan respons JSON
    return redirect()->back()->with('success', 'Marker berhasil ditambahkan!');
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
