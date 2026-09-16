<?php

namespace App\Observers;

use App\Models\Peminjaman;
use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Auth;

class PeminjamanObserver
{
    /**
     * Helper privat untuk mencatat log ke tabel log_aktivitas.
     */
    private function catatLog(string $pesan): void
    {
        if (Auth::check()) {
            LogAktivitas::create([
                'user_id' => Auth::id(),
                'aktivitas' => $pesan,
            ]);
        }
    }

    /**
     * Menangani event "created" pada model Peminjaman.
     */
    public function created(Peminjaman $peminjaman): void
    {
        $namaPeminjam = $peminjaman->user?->name ?? 'User';
        $this->catatLog("Peminjam ({$namaPeminjam}) membuat permohonan peminjaman baru (ID: #{$peminjaman->id})");
    }

    /**
     * Menangani event "updated" pada model Peminjaman.
     */
    public function updated(Peminjaman $peminjaman): void
    {
        if ($peminjaman->wasChanged('status')) {
            $this->catatLog("Status peminjaman (ID: #{$peminjaman->id}) berubah menjadi: '{$peminjaman->status}'");
        } else {
            if (!empty($peminjaman->getChanges())) {
                $this->catatLog("Memperbarui detail data peminjaman (ID: #{$peminjaman->id})");
            }
        }
    }

    /**
     * Menangani event "deleted" pada model Peminjaman.
     */
    public function deleted(Peminjaman $peminjaman): void
    {
        $this->catatLog("Membatalkan/menghapus permohonan peminjaman (ID: #{$peminjaman->id})");
    }
}