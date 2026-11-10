<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil - Panel Peminjam</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

    <!-- Main Content -->
    <main class="max-w-2xl mx-auto px-6 py-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-slate-800">Edit Profil</h2>
            <p class="text-sm text-slate-500 mt-1">Perbarui data profil akun kamu.</p>
        </div>

        @include('profile._form')
    </main>
</body>
</html>
