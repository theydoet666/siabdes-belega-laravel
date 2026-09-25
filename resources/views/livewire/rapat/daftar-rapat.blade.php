<div>
    <!-- Notifikasi Sukses / Error -->
    @if ($pesanSukses)
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ $pesanSukses }}</span>
            </div>
            <button wire:click="$set('pesanSukses', null)" class="text-emerald-700 hover:text-emerald-900 cursor-pointer font-bold text-xs">✕</button>
        </div>
    @endif

    @if ($pesanError)
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>{{ $pesanError }}</span>
            </div>
            <button wire:click="$set('pesanError', null)" class="text-rose-700 hover:text-rose-900 cursor-pointer font-bold text-xs">✕</button>
        </div>
    @endif

    <!-- Header Halaman Rapat -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-tinta">Daftar Rapat Desa</h1>
            <p class="text-sm text-tinta/60 mt-1">Kelola agenda rapat kantor desa, registrasi peserta meja kiosk, dan cetak lembar hadir resmi</p>
        </div>

        <div>
            <a href="{{ route('rapat.baru') }}" 
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-daun hover:bg-daun-tua text-white text-sm font-semibold shadow-md shadow-daun/20 focus:outline-none focus:ring-2 focus:ring-daun focus:ring-offset-2 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Buat Rapat Baru</span>
            </a>
        </div>
    </div>

    <!-- Panel Kontrol & Filter -->
    <div class="bg-white rounded-2xl border border-garis shadow-sm p-4 sm:p-5 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <!-- Search Input -->
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-tinta/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="cari" 
                       type="text" 
                       placeholder="Cari judul agenda, kode rapat, atau lokasi..." 
                       class="w-full pl-10 pr-4 py-2.5 bg-kertas/50 border border-garis rounded-xl text-sm text-tinta placeholder-tinta/40 focus:outline-none focus:border-daun focus:ring-1 focus:ring-daun transition" />
            </div>

            <!-- Filter Status & Segarkan -->
            <div class="flex items-center gap-2.5">
                <select wire:model.live="filterStatus" 
                        class="py-2.5 px-3 bg-kertas/50 border border-garis rounded-xl text-sm text-tinta focus:outline-none focus:border-daun focus:ring-1 focus:ring-daun transition">
                    <option value="">Semua Status</option>
                    <option value="draft">Draft</option>
                    <option value="dibuka">Dibuka (Aktif)</option>
                    <option value="ditutup">Ditutup</option>
                </select>

                <button wire:click="segarkanData" 
                        type="button" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-garis hover:border-daun hover:text-daun bg-white text-xs font-semibold text-tinta/70 transition cursor-pointer"
                        title="Segarkan Data">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span class="hidden sm:inline">Segarkan</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Rapat -->
    <div class="bg-white rounded-2xl border border-garis shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-kertas/80 border-b border-garis text-xs font-bold text-tinta/70 uppercase tracking-wider">
                        <th class="py-3.5 px-4 sm:px-6">Kode</th>
                        <th class="py-3.5 px-4">Agenda Rapat</th>
                        <th class="py-3.5 px-4">Waktu & Tempat</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Kehadiran</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-garis/60">
                    @forelse ($daftarRapat as $rapat)
                        <tr class="hover:bg-kertas/30 transition">
                            <!-- Kode -->
                            <td class="py-4 px-4 sm:px-6">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-daun/10 text-daun border border-daun/20 font-mono font-bold text-xs tracking-wider">
                                    {{ $rapat->kode }}
                                </span>
                            </td>

                            <!-- Judul & Penyelenggara -->
                            <td class="py-4 px-4">
                                <a href="{{ route('rapat.detail', $rapat->id) }}" class="font-semibold text-tinta hover:text-daun transition block line-clamp-1">
                                    {{ $rapat->judul }}
                                </a>
                                <p class="text-xs text-tinta/50 mt-0.5">{{ $rapat->penyelenggara }}</p>
                            </td>

                            <!-- Waktu & Tempat -->
                            <td class="py-4 px-4 text-xs text-tinta/80">
                                <div class="font-medium text-tinta">{{ $rapat->tanggal?->translatedFormat('d M Y') }}</div>
                                <div class="text-tinta/50 mt-0.5">{{ $rapat->tempat }}</div>
                            </td>

                            <!-- Status -->
                            <td class="py-4 px-4">
                                @if ($rapat->status === \App\Enums\StatusRapat::DRAFT)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                        Draft
                                    </span>
                                @elseif ($rapat->status === \App\Enums\StatusRapat::DIBUKA)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-daun-tua border border-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Dibuka
                                    </span>
                                @elseif ($rapat->status === \App\Enums\StatusRapat::DITUTUP)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-300">
                                        Ditutup
                                    </span>
                                @endif
                            </td>

                            <!-- Kehadiran -->
                            <td class="py-4 px-4 text-xs">
                                <div class="font-semibold text-tinta">
                                    <span class="text-daun">{{ $rapat->kehadiran_count ?? 0 }}</span> / {{ $rapat->undangan_count ?? 0 }}
                                </div>
                                <div class="text-[11px] text-tinta/50">
                                    @php
                                        $persen = ($rapat->undangan_count > 0) ? round(($rapat->kehadiran_count / $rapat->undangan_count) * 100) : 0;
                                    @endphp
                                    {{ $persen }}% hadir
                                </div>
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-4 sm:px-6 text-right">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <!-- Kelola Detail -->
                                    <a href="{{ route('rapat.detail', $rapat->id) }}" 
                                       class="px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-daun hover:bg-daun-tua text-white transition shadow-xs">
                                        Detail
                                    </a>

                                    <!-- QR Modal Trigger -->
                                    <button wire:click="$dispatch('buka-panel-qr', { rapatId: '{{ $rapat->id }}' })" 
                                            type="button" 
                                            class="p-1.5 rounded-lg border border-garis hover:border-daun hover:text-pena text-tinta/70 transition cursor-pointer"
                                            title="Tampilkan QR Mandiri">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                    </button>

                                    <!-- Cetak A4 Trigger -->
                                    <a href="{{ route('rapat.cetak', $rapat->id) }}" 
                                       target="_blank" 
                                       class="p-1.5 rounded-lg border border-garis hover:border-emerald-600 hover:text-emerald-700 text-tinta/70 transition cursor-pointer"
                                       title="Cetak Lembar A4">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                    </a>

                                    <!-- Ekspor Excel Trigger -->
                                    <a href="{{ route('rapat.ekspor-excel', $rapat->id) }}" 
                                       class="p-1.5 rounded-lg border border-garis hover:border-emerald-600 hover:text-emerald-700 text-tinta/70 transition cursor-pointer"
                                       title="Ekspor Rekap Excel">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </a>

                                    <!-- Duplikasi Modal Trigger -->
                                    <button wire:click="$dispatch('buka-modal-duplikasi', { rapatId: '{{ $rapat->id }}' })" 
                                            type="button" 
                                            class="p-1.5 rounded-lg border border-garis hover:border-kuning hover:text-kuning text-tinta/70 transition cursor-pointer"
                                            title="Duplikasi Rapat">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>

                                    <!-- Hapus (Khusus Admin) -->
                                    @can('delete', $rapat)
                                        <button wire:click="konfirmasiHapus('{{ $rapat->id }}')" 
                                                type="button" 
                                                class="p-1.5 rounded-lg border border-rose-200 hover:bg-rose-50 text-rose-600 transition cursor-pointer"
                                                title="Hapus Rapat">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-4 text-center text-tinta/50">
                                <div class="max-w-sm mx-auto space-y-3">
                                    <svg class="w-12 h-12 mx-auto text-tinta/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="font-medium text-sm text-tinta/70">Belum ada agenda rapat</p>
                                    <p class="text-xs text-tinta/40">Mulai dengan membuat agenda rapat pertama untuk memulai absensi digital.</p>
                                    <div class="pt-2">
                                        <a href="{{ route('rapat.baru') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-daun hover:bg-daun-tua text-white text-xs font-semibold shadow-sm transition">
                                            + Buat Rapat Pertama
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginasi -->
        @if ($daftarRapat->hasPages())
            <div class="px-6 py-4 border-t border-garis bg-kertas/30">
                {{ $daftarRapat->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Konfirmasi Hapus Rapat -->
    @if ($bukaModalHapus)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto bg-tinta/60 backdrop-blur-xs">
            <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-garis overflow-hidden p-6 text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center border border-rose-200">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-tinta">Hapus Rapat Permanen?</h3>
                <p class="text-xs text-tinta/70 mt-2">
                    Anda akan menghapus rapat <strong class="text-tinta font-bold">{{ $judulRapatHapus }}</strong> beserta seluruh berkas tanda tangan & foto kehadiran terkait. Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="mt-6 flex items-center justify-center gap-3">
                    <button wire:click="batalHapus" type="button" class="px-4 py-2.5 rounded-xl border border-garis text-xs font-medium text-tinta/80 hover:bg-kertas transition cursor-pointer">
                        Batal
                    </button>
                    <button wire:click="eksekusiHapus" wire:loading.attr="disabled" type="button" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition cursor-pointer disabled:opacity-50">
                        <span wire:loading.remove>Ya, Hapus Permanen</span>
                        <span wire:loading>Menghapus...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Sub-komponen Modal QR & Duplikasi -->
    <livewire:rapat.panel-qr />
    <livewire:rapat.modal-duplikasi-rapat />
</div>
