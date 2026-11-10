@if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
        <ul class="list-disc pl-5 mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $back = $user->role === 'admin'
        ? route('admin.dashboard')
        : ($user->role === 'petugas' ? route('petugas.peminjaman.index') : route('peminjam.katalog'));
@endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="flex items-center gap-4 mb-6 pb-6 border-b border-gray-100">
            @if($user->foto_profile)
                <img src="{{ Storage::url($user->foto_profile) }}" alt="Foto Profil"
                    class="w-24 h-24 rounded-full object-cover border border-gray-200">
            @else
                <div class="w-24 h-24 rounded-full bg-blue-600 text-white flex items-center justify-center text-3xl font-bold">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Foto Profil
                    <span class="text-xs text-gray-400 font-normal">(Biarkan kosong jika tidak ingin mengubah foto)</span>
                </label>
                <input type="file" name="foto_profile" accept="image/jpeg,image/png,image/jpg"
                    class="text-sm text-gray-600 file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-600 file:font-semibold hover:file:bg-blue-100">
            </div>
        </div>

        <div class="mb-5">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Nama</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-5">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-5">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Password Baru
                <span class="text-xs text-gray-400 font-normal">(Kosongkan jika tidak ingin mengubah password)</span>
            </label>
            <input type="password" name="password"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-5">
            <label class="block text-gray-700 text-sm font-semibold mb-2">No. HP</label>
            <input type="text" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" maxlength="15"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Alamat</label>
            <textarea name="alamat" rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('alamat', $user->alamat) }}</textarea>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ $back }}"
                class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2 rounded-lg text-sm font-semibold transition">Batal</a>
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-semibold transition">Simpan</button>
        </div>
    </form>
</div>
