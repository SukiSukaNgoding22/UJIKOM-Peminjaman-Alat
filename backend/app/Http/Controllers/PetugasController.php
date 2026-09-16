<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class PetugasController extends Controller
{
    public function indexPeminjaman()
    {
        $search = request()->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->where('status', 'diajukan')
            ->when($search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return view('petugas.peminjaman.index', compact('peminjamans', 'search'));
    }

    public function setujuPeminjaman($id)
    {
        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($id);
            $peminjaman->update(['status' => 'dipinjam']);

            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::findOrFail($detail->alat_id);
                $alat->stok -= $detail->jumlah;
                $alat->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Peminjaman berhasil disetujui dan stok alat dikurangi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function tolakPeminjaman($id)
    {
        try {
            $peminjaman = Peminjaman::findOrFail($id);

            if ($peminjaman->status == 'diajukan') {
                $peminjaman->delete();
                return redirect()->back()->with('success', 'Pengajuan peminjaman berhasil ditolak.');
            }

            return redirect()->back()->with('error', 'Status peminjaman sudah berubah.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

        public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');
        
        // Mengambil data pengembalian beserta relasinya
        $pengembalians = \App\Models\Pengembalian::with(['peminjaman.user', 'petugas', 'peminjaman.detailPinjam.alat'])
            ->when($search, function ($query, $search) {
                // Pencarian berdasarkan nama peminjam atau kondisi alat
                return $query->whereHas('peminjaman.user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('kondisi_kembali', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('petugas.pengembalian.index', compact('pengembalians', 'search'));
    }

    public function prosesPengembalian(Request $request, $peminjamanId)
    {
        $request->validate([
            'kondisi_kembali' => 'required|string',
            'denda' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($peminjamanId);

            Pengembalian::create([
                'peminjaman_id' => $peminjaman->id,
                'tgl_kembali' => now(),
                'kondisi_kembali' => $request->kondisi_kembali,
                'denda' => $request->denda ?? 0,
                'petugas_id' => auth()->id(),
            ]);

            $peminjaman->update(['status' => 'dikembalikan']);

            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::findOrFail($detail->alat_id);
                $alat->stok += $detail->jumlah;
                $alat->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Pengembalian berhasil dicatat dan stok dipulihkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function indexLaporan(Request $request)
    {
        $query = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian']);

        // Filter Rentang Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tgl_pinjam', [$request->start_date, $request->end_date]);
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $peminjamans = $query->latest()->paginate(15)->withQueryString();
        return view('petugas.laporan.index', compact('peminjamans'));
    }

    // Memproses cetak/download PDF
    public function cetakLaporan(Request $request)
    {
        $query = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tgl_pinjam', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $peminjamans = $query->latest()->get(); // Tarik semua data tanpa paginasi untuk dicetak
        
        // Load view PDF
        $pdf = Pdf::loadView('petugas.laporan.pdf', compact('peminjamans', 'request'));
        
        // Mengunduh/membuka file PDF
        return $pdf->stream('Laporan-Peminjaman-'.date('Y-m-d').'.pdf');
    }
}
