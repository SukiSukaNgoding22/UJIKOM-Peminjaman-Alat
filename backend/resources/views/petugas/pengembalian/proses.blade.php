@extends('layouts.app')

@section('title', 'Proses Pengembalian')
@section('header-title', 'Form Verifikasi Pengembalian Alat')

@section('content')
    {{-- Notifikasi Sukses --}}
    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Notifikasi Error --}}
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg shadow-sm text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 max-w-4xl">
        

        <div class="p-6">
            {{-- Informasi Peminjaman --}}
            <div class="mb-6 bg-gray-50 border border-gray-200 rounded-lg p-5">
                <h4 class="font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">Detail Peminjaman</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Nama Peminjam</span>
                        <span class="font-medium text-gray-900">{{ $peminjaman->user->name ?? 'User Dihapus' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Tanggal Pinjam</span>
                        <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($peminjaman->tgl_pinjam ?? $peminjaman->tanggal_pinjam)->format('d M Y') }}</span>
                    </div>
                    <div class="md:col-span-2 mt-2">
                        <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Daftar Alat</span>
                        <ul class="list-disc list-inside space-y-1 text-gray-800 bg-white p-3 rounded border border-gray-200">
                            {{-- Looping daftar alat agar tidak error "nama_alat on null" --}}
                            @forelse($peminjaman->detailPinjam as $detail)
                                <li>
                                    <span class="font-semibold">{{ $detail->alat->nama_alat ?? 'Alat Telah Dihapus' }}</span>
                                    <span class="text-gray-500">(Jumlah: {{ $detail->jumlah }})</span>
                                </li>
                            @empty
                                <li class="text-gray-500 italic">Tidak ada detail alat yang ditemukan.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Form Proses (Setuju / Tolak) --}}
            <form action="{{ route('petugas.pengembalian.proses', $peminjaman->id) }}" method="POST">
                @csrf
                
                <div class="mb-5">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Kondisi Barang Saat Dikembalikan <span class="text-red-500">*</span></label>
                    <select name="kondisi_kembali" required class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                        <option value="baik" {{ old('kondisi_kembali') == 'baik' ? 'selected' : '' }}>Baik</option>
                        <option value="rusak ringan" {{ old('kondisi_kembali') == 'rusak ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                        <option value="rusak sedang" {{ old('kondisi_kembali') == 'rusak sedang' ? 'selected' : '' }}>Rusak Sedang</option>
                        <option value="rusak berat" {{ old('kondisi_kembali') == 'rusak berat' ? 'selected' : '' }}>Rusak Berat</option>
                    </select>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Denda (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="denda" value="0" min="0" required class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                    <p class="text-xs text-gray-500 mt-1">Biarkan 0 jika tidak ada denda keterlambatan/kerusakan.</p>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between border-t border-gray-200 pt-5 gap-3">
                    <a href="{{ route('petugas.pengembalian.menunggu') }}" class="w-full sm:w-auto text-center bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2.5 rounded-lg text-sm font-semibold transition">
                        Kembali
                    </a>
                    
                    <div class="flex flex-col sm:flex-row w-full sm:w-auto gap-3">
                        {{-- Tombol Tolak --}}
                        <button type="submit" name="aksi" value="tolak" class="w-full sm:w-auto bg-red-500 hover:bg-red-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition" onclick="return confirm('Yakin ingin menolak pengajuan pengembalian ini?')">
                            Tolak Pengajuan
                        </button>
                        
                        {{-- Tombol Setuju --}}
                        <button type="submit" name="aksi" value="setuju" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition" onclick="return confirm('Yakin menyetujui pengembalian ini?')">
                            Setujui & Selesaikan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection