<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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

            // Validasi status agar tidak double deduct stok jika disetujui 2 kali
            if ($peminjaman->status !== 'diajukan') {
                throw new \Exception("Pengajuan peminjaman ini sudah diproses sebelumnya.");
            }

            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::where('id', $detail->alat_id)->lockForUpdate()->firstOrFail();

                // Perbaikan logika stok
                if ($alat->stok < $detail->jumlah) {
                    throw new \Exception("Stok alat {$alat->nama_alat} tidak mencukupi untuk dipinjam (Sisa: {$alat->stok}, Diminta: {$detail->jumlah}).");
                }

                $alat->stok -= $detail->jumlah;
                $alat->save();
            }

            $peminjaman->update(['status' => 'dipinjam']);

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
        $pengembalians = Pengembalian::with(['peminjaman.user', 'petugas', 'peminjaman.detailPinjam.alat'])
            ->when($search, function ($query, $search) {
                // Pencarian berdasarkan nama peminjam atau kondisi alat
                return $query->where(function($q) use ($search) {
                    $q->whereHas('peminjaman.user', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('kondisi_kembali', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('petugas.pengembalian.index', compact('pengembalians', 'search'));
    }

    public function menungguPengembalian()
    {
        // HANYA ambil data yang berstatus 'menunggu_persetujuan'
        $peminjaman = Peminjaman::with(['user', 'detailPinjam'])
            ->where('status', 'menunggu_persetujuan')
            ->get();
    
        return view('petugas.pengembalian.menunggu', compact('peminjaman'));
    }

    public function halamanProses($id)
    {
        // Ambil data peminjaman beserta relasi user dan barangnya
        $peminjaman = Peminjaman::with(['user', 'detailPinjam.alat'])->findOrFail($id);

        // Cegah petugas masuk ke halaman ini jika statusnya bukan menunggu persetujuan
        if ($peminjaman->status !== 'menunggu_persetujuan') {
            return redirect()->route('petugas.pengembalian.index')
                ->with('error', 'Status barang ini tidak sedang menunggu persetujuan.');
        }

        // Arahkan ke file blade baru (sesuaikan foldernya jika berbeda)
        return view('petugas.pengembalian.proses', compact('peminjaman'));
}

    public function prosesPengembalian(Request $request, $id)
    {
        $peminjaman = Peminjaman::with('detailPinjam.alat')->findOrFail($id);

        if (strtolower($peminjaman->status) !== 'menunggu_persetujuan') {
            return redirect()->back()->with('error', 'Gagal memproses! Status data tidak valid.');
        }

        DB::beginTransaction();
        try {
            // --- JIKA PETUGAS KLIK SETUJU ---
            if ($request->aksi == 'setuju') {
                $request->validate([
                    'kondisi_kembali' => 'required',
                    'denda' => 'required|numeric'
                ]);

                // 1. Update status di tabel Peminjaman
                $peminjaman->status = 'dikembalikan';
                $peminjaman->save(); 

                // 2. Kembalikan stok alat
                foreach ($peminjaman->detailPinjam as $detail) {
                    $detail->alat->increment('stok', $detail->jumlah);
                }

                // 3. Buat record baru di tabel Pengembalian
                Pengembalian::create([
                    'peminjaman_id' => $peminjaman->id,
                    'kondisi_kembali' => $request->kondisi_kembali,
                    'denda' => $request->denda,
                    'tgl_kembali' => now(),
                    'petugas_id' => Auth::id()
                ]);
                
                DB::commit();
                return redirect()->route('petugas.pengembalian.menunggu')
                                ->with('success', 'Pengembalian disetujui, stok dipulihkan, dan data tersimpan.');
            }

            // --- JIKA PETUGAS KLIK TOLAK ---
            if ($request->aksi == 'tolak') {
                // Langsung ubah statusnya saja, stok tetap berkurang (masih dipinjam)
                $peminjaman->status = 'dipinjam'; 
                $peminjaman->save();

                DB::commit();
                return redirect()->route('petugas.pengembalian.menunggu')
                                ->with('success', 'Pengajuan pengembalian ditolak. Status kembali dipinjam.');
            }

            throw new \Exception('Aksi tidak dikenali oleh sistem.');
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

    public function exportExcel(Request $request)
    {
        $query = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tgl_pinjam', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $peminjamans = $query->latest()->get();

        $filename = "Laporan_Peminjaman_" . date('Ymd_His') . ".csv";

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $callback = function() use($peminjamans) {
            $file = fopen('php://output', 'w');
            
            // Tambahkan BOM untuk UTF-8 agar excel membacanya dengan benar
            fputs($file, "\xEF\xBB\xBF");
            
            // Header menggunakan delimiter semicolon
            fputcsv($file, ['ID', 'Peminjam', 'Tgl Pinjam', 'Tgl Kembali (Pengajuan)', 'Status', 'Denda', 'Tgl Dikembalikan (Aktual)'], ';');

            foreach ($peminjamans as $row) {
                $denda = $row->pengembalian ? $row->pengembalian->denda : 0;
                $tglDikembalikan = $row->pengembalian ? $row->pengembalian->tgl_kembali : '-';

                fputcsv($file, [
                    $row->id,
                    $row->user->name ?? '-',
                    $row->tgl_pinjam,
                    $row->tgl_kembali,
                    $row->status,
                    $denda,
                    $tglDikembalikan
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
