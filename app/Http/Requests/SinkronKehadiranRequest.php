<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SinkronKehadiranRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diotorisasi untuk membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Persiapkan input sebelum validasi dijalankan.
     */
    protected function prepareForValidation(): void
    {
        // Dukung pengiriman single object check-in tanpa pembungkus items
        if (! $this->has('items') && $this->has('rapat_id')) {
            $this->merge([
                'items' => [$this->all()],
            ]);
        }
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan ini.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.id' => ['nullable', 'uuid'],
            'items.*.rapat_id' => ['required', 'uuid', 'exists:rapat,id'],
            'items.*.undangan_id' => ['nullable', 'uuid'],
            'items.*.jalur' => ['nullable', 'in:kiosk,mandiri,operator'],
            'items.*.perangkat_id' => ['nullable', 'string', 'max:100'],
            'items.*.waktu_perangkat' => ['nullable'],
            'items.*.diwakili_oleh' => ['nullable', 'string', 'max:120'],
            'items.*.undangan_baru' => ['nullable', 'array'],
            'items.*.undangan_baru.nama' => ['required_with:items.*.undangan_baru', 'string', 'max:120'],
            'items.*.undangan_baru.jabatan' => ['nullable', 'string', 'max:120'],
            'items.*.undangan_baru.instansi' => ['nullable', 'string', 'max:120'],
            'items.*.undangan_baru.hp' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'items.*.ttd' => [
                'required',
                'string',
                'regex:/^data:image\/(png|jpeg);base64,/',
                function (string $attribute, mixed $value, Closure $fail) {
                    if (! is_string($value)) {
                        return;
                    }
                    [, $data] = explode(';base64,', $value, 2) + [1 => ''];
                    $decoded = base64_decode($data, true);
                    if ($decoded === false) {
                        $fail('Format data tanda tangan base64 tidak valid.');

                        return;
                    }
                    // Maksimal 300 KB
                    if (strlen($decoded) > 300 * 1024) {
                        $fail('Ukuran tanda tangan melebihi batas maksimal 300 KB.');

                        return;
                    }

                    $info = @getimagesizefromstring($decoded);
                    if ($info === false || ! in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG], true)) {
                        $fail('Berkas tanda tangan bukan gambar PNG atau JPEG yang valid.');
                    }
                },
            ],
            'items.*.foto' => [
                'nullable',
                'string',
                'regex:/^data:image\/(png|jpeg);base64,/',
                function (string $attribute, mixed $value, Closure $fail) {
                    if (! is_string($value) || empty($value)) {
                        return;
                    }
                    [, $data] = explode(';base64,', $value, 2) + [1 => ''];
                    $decoded = base64_decode($data, true);
                    if ($decoded === false) {
                        $fail('Format data foto base64 tidak valid.');

                        return;
                    }
                    // Maksimal 1 MB
                    if (strlen($decoded) > 1024 * 1024) {
                        $fail('Ukuran foto melebihi batas maksimal 1 MB.');

                        return;
                    }

                    $info = @getimagesizefromstring($decoded);
                    if ($info === false || ! in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG], true)) {
                        $fail('Berkas foto bukan gambar PNG atau JPEG yang valid.');
                    }
                },
            ],
        ];
    }

    /**
     * Pesan kustom untuk validasi sinkronisasi absensi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Daftar item absensi wajib diisi.',
            'items.max' => 'Maksimal 50 item absensi per permintaan sinkronisasi.',
            'items.*.rapat_id.required' => 'ID rapat wajib disertakan.',
            'items.*.rapat_id.exists' => 'Rapat tidak ditemukan.',
            'items.*.ttd.required' => 'Tanda tangan kehadiran wajib disertakan.',
            'items.*.ttd.regex' => 'Format tanda tangan harus berupa data-URL gambar PNG atau JPEG base64.',
            'items.*.foto.regex' => 'Format foto wajah harus berupa data-URL gambar PNG atau JPEG base64.',
            'items.*.undangan_baru.hp.regex' => 'Format nomor HP hanya boleh berisi angka, spasi, tanda tambah (+), dan tanda minus (-).',
        ];
    }
}
