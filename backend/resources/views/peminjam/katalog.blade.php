<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Alat Peminjam</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .custom-checkbox {
            width: 22px;
            height: 22px;
            cursor: pointer;
        }
        /* Efek hover pada kartu ala e-commerce */
        .product-card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        /* Memastikan gambar memenuhi satu kotak penuh tanpa celah */
        .product-img {
            height: 200px;
            object-fit: contain;
            width: 100%;
            background-color: #f1f5f9;
            padding: 8px;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800">

    <!-- Top Navigation Bar -->
    <header class="bg-blue-600 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-6 py-3 flex justify-between items-center">
            <h1 class="text-xl font-bold tracking-wide">Panel Peminjam</h1>
            <div class="relative">
                <button type="button" onclick="document.getElementById('peminjamMenu').classList.toggle('hidden')"
                    class="p-2 rounded-lg hover:bg-white/20 transition" aria-label="Menu">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div id="peminjamMenu" class="hidden absolute right-0 mt-2 w-48 bg-white text-slate-700 rounded-lg shadow-lg py-2 z-50">
                    <a href="{{ route('peminjam.katalog') }}" class="block px-4 py-2 text-sm hover:bg-slate-100">
                        <i class="fa-solid fa-toolbox mr-2 text-blue-600"></i> Katalog Alat
                    </a>
                    <a href="{{ route('peminjam.riwayat') }}" class="block px-4 py-2 text-sm hover:bg-slate-100">
                        <i class="fa-solid fa-clock-rotate-left mr-2 text-blue-600"></i> Riwayat Pinjam
                    </a>
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-slate-100">
                        <i class="fa-solid fa-user mr-2 text-blue-600"></i> Profil
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm hover:bg-slate-100 text-red-600">
                            <i class="fa-solid fa-right-from-bracket mr-2"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <script>
        document.addEventListener('click', function (e) {
            const menu = document.getElementById('peminjamMenu');
            if (menu && !e.target.closest('.relative')) menu.classList.add('hidden');
        });
    </script>

    <!-- KONTEN UTAMA -->
    <div class="container mt-8 mb-12">
        
        @if (session('success'))
            <div class="alert alert-success shadow-sm rounded-lg border-0 border-start border-success border-4">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger shadow-sm rounded-lg border-0 border-start border-danger border-4">{{ session('error') }}</div>
        @endif

        <div class="d-flex justify-content-between items-end mb-4">
            <h3 class="m-0 text-2xl font-bold text-slate-800">Katalog Alat Tersedia</h3>
        </div>

        <form action="{{ route('peminjam.ajukan') }}" method="POST">
            @csrf
            
            <!-- Input Tanggal Kembali -->
            <div class="card shadow-sm border-0 rounded-xl mb-4 bg-white p-4">
                <div class="row items-center">
                    <div class="col-md-5">
                        <label class="form-label font-semibold text-slate-700">Rencana Tanggal Kembali <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-slate-100"><i class="fa-regular fa-calendar"></i></span>
                            <input type="date" name="tgl_kembali_plan" class="form-control" min="{{ now()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GRID PRODUK (Ala Toko Online) -->
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mb-4">
                @forelse($alats as $alat)
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0 rounded-xl overflow-hidden product-card">
                            
                            <!-- Gambar Alat -->
                            <!-- Pastikan di database tabel alat ada kolom 'gambar', jika kosong pakai placehold.co -->
                            <img src="{{ asset($alat->gambar) }}" alt="{{ $alat->nama_alat }}" class="card-img-top product-img border-bottom" alt="{{ $alat->nama_alat }}">
                            
                            <div class="card-body d-flex flex-column p-3">
                                <div class="mb-2">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill text-xs">
                                        {{ $alat->kategori->nama_kategori }}
                                    </span>
                                </div>
                                <h5 class="card-title text-base font-bold text-slate-800 mb-1 leading-tight line-clamp-2">
                                    {{ $alat->nama_alat }}
                                </h5>
                                
                                <div class="mt-auto pt-3 flex justify-between items-center text-sm">
                                    <span class="text-slate-500">Stok Tersedia:</span>
                                    @if($alat->stok > 5)
                                        <span class="text-success font-bold">{{ $alat->stok }} pcs</span>
                                    @else
                                        <span class="text-danger font-bold">{{ $alat->stok }} pcs</span>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Footer Cart (Pilih & Jumlah) -->
                            <div class="card-footer bg-slate-50 border-top p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="form-check m-0 d-flex align-items-center gap-2">
                                        <input class="form-check-input custom-checkbox m-0" type="checkbox" name="alat_id[]" value="{{ $alat->id }}" id="alat_{{ $alat->id }}">
                                        <label class="form-check-label font-medium cursor-pointer select-none" for="alat_{{ $alat->id }}">
                                            Pilih
                                        </label>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <input type="number" name="jumlah[{{ $alat->id }}]" class="form-control text-center shadow-sm" value="1" min="1" max="{{ $alat->stok }}" style="width: 70px; height: 35px;">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-10">
                        <i class="fa-solid fa-box-open fa-3x mb-3 text-slate-300"></i><br>
                        <h5 class="text-slate-500">Belum ada alat yang tersedia saat ini.</h5>
                    </div>
                @endforelse
            </div>

            <!-- Tombol Submit Bawah -->
            <div class="d-flex justify-content-end sticky-bottom pb-4">
                <div class="bg-white p-3 rounded-xl shadow-lg border">
                    <button type="submit" class="btn btn-primary px-5 py-2 font-bold rounded-lg shadow-sm">
                        <i class="fa-solid fa-cart-arrow-down mr-2"></i> Ajukan Peminjaman
                    </button>
                </div>
            </div>

        </form>
    </div>

</body>
</html>