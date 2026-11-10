<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;


class AuthController extends Controller
{
    # Menampilkan Halaman Login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    # Memproses Login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            # Redirect User Berdasarkan Role
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role === 'petugas') {
                return redirect()->route('petugas.peminjaman.index');
            } elseif ($user->role === 'peminjam') {
                return redirect()->route('peminjam.katalog');
            }

            Auth::logout();
            return redirect()->route('login')->with('error', 'Role tidak ditemukan.');
        }

        return back()->withErrors([
            'email' => 'Email atau Password salah.',
        ])->onlyInput('email');
    }

    # Proses logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    # Menampilkan Halaman Registrasi
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    # Proses Registrasi
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'peminjam',
        ]);

        Auth::login($user);

        return redirect()->route('peminjam.katalog')->with('success', 'Registrasi berhasil! Selamat datang.');
    }

    # Menampilkan Halaman Edit Profil
    public function editProfile(Request $request)
    {
        $user = $request->user();
        $view = $user->role === 'peminjam' ? 'profile.edit' : 'profile.edit_staff';
        return view($view, compact('user'));
    }

    # Memproses Update Profil
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $request->user()->id,
            'password' => 'nullable|string|min:8',
            'no_hp' => 'nullable|string|max:15',
            'alamat' => 'nullable|string',
            'foto_profile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = $request->user();

        if ($request->hasFile('foto_profile')) {
            if ($user->foto_profile) {
                Storage::disk('public')->delete($user->foto_profile);
            }

            $validated['foto_profile'] = $request->file('foto_profile')->store('foto_profile', 'public');
        } else {
            unset($validated['foto_profile']);
        }

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        $updated = $user->update($validated);

        if (!$updated) {
            return back()->withErrors(['error' => 'Gagal mengupdate profil. Silakan coba lagi.'])->withInput();
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
