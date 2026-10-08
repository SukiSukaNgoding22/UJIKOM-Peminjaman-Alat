@extends('layouts.app')

@section('title', 'Persetujuan Pengembalian')
@section('header-title', 'Daftar Antrean Persetujuan Pengembalian')

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

    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        {{-- Header & Form Pencarian --}}
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h3 class="text-lg font-bold text-gray-800">Menunggu Verifikasi Pengembalian</h3>
            
            {{-- Form Pencarian (Arahkan routenya ke halaman ini sendiri) --}}
            <form action="{{ route('petugas.pengembalian.menunggu') }}" method="GET" class="flex w-full md:w-80">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama peminjam..."
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 text-sm font-semibold rounded-r-lg transition">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('petugas.pengembalian.menunggu') }}"
                        class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-2 text-sm rounded-lg flex items-center transition">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Tabel Data Pengembalian --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                        <th class="py-3 px-4 border-b">No</th>
                        <th class="py-3 px-4 border-b">Peminjam</th>
                        <th class="py-3 px-4 border-b">Nama Barang</th>
                        <th class="py-3 px-4 border-b">Tanggal Pinjam</th>
                        <th class="py-3 px-4 border-b">Status</th>
                        <th class="py-3 px-4 border-b text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    @forelse($peminjaman as $index => $item)
                        <tr class="hover:bg-gray-50 transition align-top">
                            <td class="py-3 px-4 border-b text-gray-900">
                                {{ $index + 1 }}
                            </td>
                            <td class="py-3 px-4 border-b font-medium text-gray-900">
                                {{ $item->user->name ?? 'User Dihapus' }}
                            </td>
                            <td class="py-3 px-4 border-b">
                                <ul class="list-disc list-inside">
                                    @foreach($item->detailPinjam as $detail)
                                        <li>{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }} ({{ $detail->jumlah }})</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="py-3 px-4 border-b">{{ \Carbon\Carbon::parse($item->tgl_pinjam)->format('d M Y') }}</td>
                            <td class="py-3 px-4 border-b">
                                <span class="text-xs font-semibold text-yellow-700 bg-yellow-100 px-2.5 py-1 rounded">
                                    Menunggu Persetujuan
                                </span>
                            </td>
                            <td class="py-3 px-4 border-b text-center">
                                <a href="{{ route('petugas.pengembalian.halaman_proses', $item->id) }}"
                                   class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded text-xs font-semibold transition shadow-sm">
                                    Proses Pengembalian
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-gray-500">Tidak ada pengajuan pengembalian saat ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection