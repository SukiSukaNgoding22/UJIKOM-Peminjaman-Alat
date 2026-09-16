<?php

namespace App\Http\Requests\Alat;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAlatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kategori_id' => ['required', 'integer', Rule::exists('kategori', 'id')],
            'nama_alat' => ['required', 'string', 'max:255'],
            'stok' => ['required', 'integer', 'min:0'],
            'status_kondisi' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string',],
            'gambar' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ];
    }

    public function message(): array
    {
        return [
            'kategori_id.exists' => 'Kategori yang dipilih tidak valid atau tidak terdaftar.',
            'stok.min' => 'Stok tidak boleh kurang dari 0.',
            'gambar.max' => 'Ukuran gambar tidak boleh lebih dari 2MB.',
            'gambar.image' => 'File yang diunggah harus berupa gambar.',
        ];
    }
}
