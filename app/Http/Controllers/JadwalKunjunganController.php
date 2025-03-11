<?php

namespace App\Http\Controllers;

use App\Models\JadwalKunjungan;
use App\Models\KunjunganPetugas;
use App\Models\User;
use Illuminate\Http\Request;

class JadwalKunjunganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sekolahUsers = User::where('role', 'sekolah')->pluck('id');
        $kunjunganPetugas = KunjunganPetugas::with('petugas')->where('status', 0)->get();
        $jadwalKunjungan = JadwalKunjungan::whereIn('user_id', $sekolahUsers)->get();

        return view('dashboard.manajemen-kegiatan.jadwal-kunjungan.index', [
            'jadwalKunjungan' => $jadwalKunjungan,
            'kunjunganPetugas' => $kunjunganPetugas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sekolahUsers = User::where('role', 'sekolah')->get(); // Mengambil daftar pengguna dengan peran "sekolah"

        return view('dashboard.manajemen-kegiatan.jadwal-kunjungan.create', [
            'sekolahUsers' => $sekolahUsers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|integer',
            'tgl_kunjungan' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ], [
            'tgl_kunjungan.required' => 'Jadwal kunjungan harus diisi hari dan setelah hari ini',
            'jam_selesai.after' => 'Jam selesai harus lebih dari jam mulai',
        ]);

        // Cek apakah jadwal kunjungan dengan tanggal dan jam yang sama sudah ada
        $existingJadwal = JadwalKunjungan::where('tgl_kunjungan', $validatedData['tgl_kunjungan'])
            ->where(function ($query) use ($validatedData) {
                $query->whereBetween('jam_mulai', [$validatedData['jam_mulai'], $validatedData['jam_selesai']])
                    ->orWhereBetween('jam_selesai', [$validatedData['jam_mulai'], $validatedData['jam_selesai']])
                    ->orWhere(function ($query) use ($validatedData) {
                        $query->where('jam_mulai', '<=', $validatedData['jam_mulai'])
                            ->where('jam_selesai', '>=', $validatedData['jam_selesai']);
                    });
            })
            ->exists();

        if ($existingJadwal) {
            return redirect()
                ->back()
                ->withErrors(['message' => 'Jadwal kunjungan pada tanggal dan jam tersebut sudah ada.'])
                ->withInput();
        }

        // Jika tidak ada konflik, buat jadwal kunjungan baru
        JadwalKunjungan::create([
            'user_id' => $validatedData['user_id'],
            'tgl_kunjungan' => $validatedData['tgl_kunjungan'],
            'jam_mulai' => $validatedData['jam_mulai'],
            'jam_selesai' => $validatedData['jam_selesai'],
        ]);

        return redirect('dashboard-jadwal-kunjungan');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $jadwalKunjungan = JadwalKunjungan::findOrFail($id);

        return view('dashboard.manajemen-kegiatan.jadwal-kunjungan.edit', [
            'jadwalKunjungan' => $jadwalKunjungan,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $jadwalKunjungan = JadwalKunjungan::findOrFail($id);

        $validatedData = $request->validate([
            'user_id' => 'required|integer',
            'tgl_kunjungan' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ], [
            'tgl_kunjungan.required' => 'Jadwal kunjungan harus diisi hari dan setelah hari ini',
        ]);

        // Cek apakah ada konflik dengan jadwal kunjungan lain
        $existingJadwal = JadwalKunjungan::where('tgl_kunjungan', $validatedData['tgl_kunjungan'])
            ->where(function ($query) use ($validatedData) {
                $query->whereBetween('jam_mulai', [$validatedData['jam_mulai'], $validatedData['jam_selesai']])
                    ->orWhereBetween('jam_selesai', [$validatedData['jam_mulai'], $validatedData['jam_selesai']])
                    ->orWhere(function ($query) use ($validatedData) {
                        $query->where('jam_mulai', '<=', $validatedData['jam_mulai'])
                            ->where('jam_selesai', '>=', $validatedData['jam_selesai']);
                    });
            })
            ->exists();

        if ($existingJadwal) {
            return redirect()
                ->back()
                ->withErrors(['message' => 'Jadwal kunjungan pada tanggal dan jam tersebut sudah ada.'])
                ->withInput();
        }

        // Update jadwal kunjungan
        $jadwalKunjungan->update([
            'user_id' => $validatedData['user_id'],
            'tgl_kunjungan' => $validatedData['tgl_kunjungan'],
            'jam_mulai' => $validatedData['jam_mulai'],
            'jam_selesai' => $validatedData['jam_selesai'],
        ]);

        return redirect('dashboard-jadwal-kunjungan');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $jadwalKunjungan = JadwalKunjungan::findOrFail($id);
        $jadwalKunjungan->delete();

        return redirect('dashboard-jadwal-kunjungan');
    }
}
