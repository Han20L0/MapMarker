<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Marker;
use Illuminate\Support\Facades\Storage;

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
    public function update(Request $request)
    {
        // Validasi input
        $request->validate([
            'id' => 'required|exists:markers,id',
            'quarantine' => 'required|string',
            'disease' => 'required|string',
            'commodity' => 'required|string',
            'information' => 'nullable|string',
            'color' => 'required|string',
            'date_found' => 'required|date',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Temukan marker berdasarkan ID
        $marker = Marker::find($request->id);

        // Update data marker
        $marker->quarantine = $request->quarantine;
        $marker->disease = $request->disease;
        $marker->commodity = $request->commodity;
        $marker->information = $request->information;
        $marker->color = $request->color;
        $marker->date_found = $request->date_found;

        // Jika ada foto baru, simpan dan update path
        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($marker->photo_path) {
                Storage::delete($marker->photo_path);
            }
            // Simpan foto baru
            $path = $request->file('photo')->store('photos', 'public');
            $marker->photo_path = $path;
        }

        // Simpan perubahan
        $marker->save();

        // Redirect atau kembali dengan pesan sukses
        return redirect()->back()->with('success', 'Marker berhasil diperbarui.');
    }
    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:markers,id',
        ]);

        $marker = Marker::findOrFail($request->id);

        // Hapus foto jika ada
        if ($marker->photo_path) {
            \Storage::disk('public')->delete($marker->photo_path);
        }

        // Hapus marker dari database
        $marker->delete();

        return redirect()->back()->with('success', 'Marker berhasil dihapus!');
    }
}
