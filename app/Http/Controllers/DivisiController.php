<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use Illuminate\Http\Request;

class DivisiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $divisi = Divisi::all();

        return view('dashboard.manajemen-pengguna.divisi.index', [
            'divisis' => $divisi,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $divisis = Divisi::all(); // Mendapatkan semua data divisi dari database

        return view('dashboard.manajemen-pengguna.divisi.create', compact('divisis'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_divisi' => 'required|unique:divisis,nama_divisi',
        ], [
            'nama_divisi.unique' => 'Divisi sudah ada, silakan tambahkan divisi lain',
        ]);

        Divisi::create([
            'nama_divisi' => $request->nama_divisi,
        ]);

        return redirect('/dashboard-divisi')->with('success', 'Divisi berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Divisi $divisi)
    {
        // Jika tidak digunakan, method ini bisa dikosongkan
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $divisi = Divisi::findOrFail($id);

        return view('dashboard.manajemen-pengguna.divisi.edit', [
            'divisis' => $divisi,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $divisi = Divisi::findOrFail($id);

        $request->validate([
            'nama_divisi' => 'required|unique:divisis,nama_divisi,' . $id,
        ], [
            'nama_divisi.unique' => 'Divisi sudah ada, silakan gunakan nama lain',
        ]);

        $divisi->update([
            'nama_divisi' => $request->nama_divisi,
        ]);

        return redirect('/dashboard-divisi')->with('success', 'Divisi berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $divisi = Divisi::findOrFail($id);
        $divisi->delete();

        return redirect('/dashboard-divisi')->with('delete', 'Divisi berhasil dihapus.');
    }
}
