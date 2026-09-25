<div>
    <!-- Notifikasi Sukses / Error -->
    @if ($pesanSukses)
        <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ $pesanSukses }}</span>
            </div>
            <button wire:click="$set('pesanSukses', null)" class="text-emerald-700 hover:text-emerald-900 cursor-pointer text-xs font-semibold">✕</button>
        </div>
    @endif

    @if ($pesanError)
        <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>{{ $pesanError }}</span>
            </div>
            <button wire:click="$set('pesanError', null)" class="text-rose-700 hover:text-rose-900 cursor-pointer text-xs font-semibold">✕</button>
        </div>
    @endif

    <!-- Panel Kontrol & Filter -->
    <div class="bg-white rounded-2xl border border-garis shadow-sm p-4 sm:p-5 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <!-- Search & Filter Peran -->
            <div class="flex flex-1 flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-tinta/40">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="cari" 
                           type="text" 
                           placeholder="Cari nama atau email operator..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-kertas/50 border border-garis rounded-xl text-sm text-tinta placeholder-tinta/40 focus:outline-none focus:border-daun focus:ring-1 focus:ring-daun transition" />
                </div>

                <div class="w-full sm:w-48">
                    <select wire:model.live="filterPeran" 
                            class="w-full py-2.5 px-3 bg-kertas/50 border border-garis rounded-xl text-sm text-tinta focus:outline-none focus:border-daun focus:ring-1 focus:ring-daun transition">
                        <option value="">Semua Peran</option>
                        <option value="admin">Administrator</option>
                        <option value="operator">Operator</option>
                    </select>
                </div>
            </div>

            <!-- Tombol Tambah Operator -->
            <div>
                <button wire:click="bukaModal" 
                        type="button" 
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-daun hover:bg-daun-tua text-white text-sm font-semibold shadow-sm shadow-daun/20 focus:outline-none focus:ring-2 focus:ring-daun focus:ring-offset-2 transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Operator</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Operator -->
    <div class="bg-white rounded-2xl border border-garis shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-kertas/80 border-b border-garis text-xs font-bold text-tinta/70 uppercase tracking-wider">
                        <th class="py-3.5 px-4 sm:px-6">Nama Operator</th>
                        <th class="py-3.5 px-4">Email</th>
                        <th class="py-3.5 px-4">Peran</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Terdaftar</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-garis/60">
                    @forelse ($operators as $op)
                        @php
                            $isSelf = auth()->user()->profil && auth()->user()->profil->id === $op->id;
                            $isAdmin = $op->peran === \App\Enums\PeranPengguna::ADMIN;
                        @endphp
                        <tr class="hover:bg-kertas/30 transition {{ $isSelf ? 'bg-emerald-50/30' : '' }}">
                            <!-- Nama -->
                            <td class="py-4 px-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-daun/10 text-daun flex items-center justify-center font-bold text-xs uppercase border border-daun/20">
                                        {{ mb_substr($op->nama, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-tinta flex items-center gap-2">
                                            <span>{{ $op->nama }}</span>
                                            @if ($isSelf)
                                                <span class="text-[10px] bg-emerald-100 text-emerald-800 font-semibold px-2 py-0.5 rounded-full border border-emerald-200">Anda</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Email -->
                            <td class="py-4 px-4 text-tinta/80 font-mono text-xs">
                                {{ $op->email }}
                            </td>

                            <!-- Peran -->
                            <td class="py-4 px-4">
                                @if ($isAdmin)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-kuning border border-amber-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        Administrator
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-pena border border-blue-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Operator
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-4 px-4">
                                @if ($op->aktif)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-daun border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-daun animate-pulse"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <!-- Terdaftar -->
                            <td class="py-4 px-4 text-xs text-tinta/60">
                                {{ $op->dibuat_pada ? $op->dibuat_pada->translatedFormat('d M Y, H:i') : '-' }} WITA
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-4 sm:px-6 text-right">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <!-- Toggle Peran -->
                                    <button wire:click="gantiPeran('{{ $op->id }}')" 
                                            wire:confirm="Yakin ingin mengubah peran '{{ $op->nama }}' menjadi {{ $op->peran === \App\Enums\PeranPengguna::ADMIN ? 'Operator' : 'Administrator' }}?"
                                            wire:loading.attr="disabled"
                                            @if ($isSelf) disabled @endif
                                            class="px-2.5 py-1.5 rounded-lg text-xs font-medium border transition {{ $isSelf ? 'opacity-40 cursor-not-allowed border-garis text-tinta/40' : 'border-garis hover:border-daun hover:text-daun text-tinta/80 bg-white hover:bg-kertas/50 cursor-pointer' }}"
                                            title="{{ $isSelf ? 'Tidak dapat mengubah peran akun sendiri' : 'Ubah peran pengguna ini' }}">
                                        Ubah Peran
                                    </button>

                                    <!-- Toggle Status Aktif/Nonaktif -->
                                    <button wire:click="toggleStatus('{{ $op->id }}')" 
                                            wire:confirm="Yakin ingin {{ $op->aktif ? 'menonaktifkan' : 'mengaktifkan' }} akun operator '{{ $op->nama }}'?"
                                            wire:loading.attr="disabled"
                                            @if ($isSelf) disabled @endif
                                            class="px-2.5 py-1.5 rounded-lg text-xs font-medium border transition {{ $isSelf ? 'opacity-40 cursor-not-allowed border-garis text-tinta/40' : ($op->aktif ? 'border-rose-200 text-rose-700 hover:bg-rose-50' : 'border-emerald-200 text-daun hover:bg-emerald-50') }} cursor-pointer"
                                            title="{{ $isSelf ? 'Tidak dapat menonaktifkan akun sendiri' : ($op->aktif ? 'Nonaktifkan operator ini' : 'Aktifkan operator ini') }}">
                                        {{ $op->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-4 text-center text-tinta/50">
                                <div class="max-w-sm mx-auto space-y-2">
                                    <svg class="w-10 h-10 mx-auto text-tinta/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <p class="font-medium text-sm text-tinta/70">Tidak ada operator ditemukan</p>
                                    <p class="text-xs text-tinta/40">Coba ubah kata kunci pencarian atau bersihkan filter peran.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginasi -->
        @if ($operators->hasPages())
            <div class="px-6 py-4 border-t border-garis bg-kertas/30">
                {{ $operators->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tambah Operator Baru -->
    @if ($bukaModalTambah)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto bg-tinta/50 backdrop-blur-xs">
            <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-garis overflow-hidden">
                <!-- Header Modal -->
                <div class="bg-daun-tua px-6 py-4 text-white flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold">Tambah Operator Baru</h3>
                        <p class="text-xs text-emerald-200/80 mt-0.5">Buat akun untuk staf atau administrator rapat desa</p>
                    </div>
                    <button wire:click="tutupModal" type="button" class="text-white/70 hover:text-white cursor-pointer text-lg font-bold p-1">
                        ✕
                    </button>
                </div>

                <!-- Form Modal -->
                <form wire:submit="simpanOperator" class="p-6 space-y-4">
                    <!-- Nama Lengkap -->
                    <div>
                        <label for="nama" class="block text-xs font-semibold text-tinta mb-1.5">
                            Nama Lengkap <span class="text-rose-600">*</span>
                        </label>
                        <input wire:model="nama" 
                               type="text" 
                               id="nama" 
                               required 
                               placeholder="contoh: I Ketut Suartana, S.E." 
                               class="w-full px-3.5 py-2.5 bg-kertas/50 border @error('nama') border-rose-300 focus:border-rose-500 @else border-garis focus:border-daun @enderror rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                        @error('nama')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-xs font-semibold text-tinta mb-1.5">
                            Alamat Email <span class="text-rose-600">*</span>
                        </label>
                        <input wire:model="email" 
                               type="email" 
                               id="email" 
                               required 
                               placeholder="operator@belega.desa.id" 
                               class="w-full px-3.5 py-2.5 bg-kertas/50 border @error('email') border-rose-300 focus:border-rose-500 @else border-garis focus:border-daun @enderror rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                        @error('email')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kata Sandi -->
                    <div>
                        <label for="password" class="block text-xs font-semibold text-tinta mb-1.5">
                            Kata Sandi Awal <span class="text-rose-600">*</span>
                        </label>
                        <input wire:model="password" 
                               type="password" 
                               id="password" 
                               required 
                               placeholder="Minimal 6 karakter" 
                               class="w-full px-3.5 py-2.5 bg-kertas/50 border @error('password') border-rose-300 focus:border-rose-500 @else border-garis focus:border-daun @enderror rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                        @error('password')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Peran -->
                    <div>
                        <label for="peran" class="block text-xs font-semibold text-tinta mb-1.5">
                            Peran Hak Akses <span class="text-rose-600">*</span>
                        </label>
                        <select wire:model="peran" 
                                id="peran" 
                                class="w-full px-3.5 py-2.5 bg-kertas/50 border @error('peran') border-rose-300 focus:border-rose-500 @else border-garis focus:border-daun @enderror rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition">
                            <option value="operator">Operator Rapat (Mengelola rapat, absensi, cetak)</option>
                            <option value="admin">Administrator Desa (Akses penuh + pengaturan & audit log)</option>
                        </select>
                        @error('peran')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tombol Aksi Modal -->
                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-garis">
                        <button wire:click="tutupModal" 
                                type="button" 
                                class="px-4 py-2.5 rounded-xl border border-garis text-sm font-medium text-tinta/80 hover:bg-kertas/60 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                wire:loading.attr="disabled"
                                class="px-5 py-2.5 rounded-xl bg-daun hover:bg-daun-tua text-white text-sm font-semibold shadow-sm shadow-daun/20 focus:outline-none focus:ring-2 focus:ring-daun focus:ring-offset-2 transition cursor-pointer disabled:opacity-50">
                            <span wire:loading.remove>Simpan Operator</span>
                            <span wire:loading>Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
