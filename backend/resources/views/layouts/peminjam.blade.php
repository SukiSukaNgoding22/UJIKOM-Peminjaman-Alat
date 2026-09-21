<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Katalog Alat - Sistem Peminjaman')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans antialiased flex flex-col min-h-screen">

    <!-- Top Navigation Bar -->
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="container mx-auto px-4 lg:px-8 flex items-center justify-between h-16">
            <!-- Logo / Brand -->
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-600 text-white flex items-center justify-center rounded-lg font-bold">
                    P
                </div>
                <span class="text-xl font-bold text-gray-800 tracking-tight">PinjamAlat</span>
            </div>

            <!-- Menu Navigasi -->
            <nav class="hidden md:flex space-x-6 text-sm font-medium text-gray-600">
                <a href="{{ route('peminjam.dashboard') }}" class="{{ request()->routeIs('peminjam.dashboard') ? 'text-blue-600' : 'hover:text-blue-600' }}">Katalog Alat</a>
                <a href="#" class="hover:text-blue-600">Riwayat Peminjaman</a>
            </nav>

            <!-- Profil & Logout -->
            <div class="flex items-center gap-4">
                <div class="text-sm font-semibold text-gray-700">
                    Hai, {{ auth()->user()->name }}
                </div>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="bg-red-50 text-red-600 hover:bg-red-100 px-3 py-1.5 rounded-lg text-sm font-semibold transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Konten Utama -->
    <main class="flex-grow container mx-auto px-4 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-6 mt-auto">
        <div class="text-center text-sm text-gray-500">
            &copy; 2026 Sistem Peminjaman Alat - UKK RPL.
        </div>
    </footer>

</body>
</html>