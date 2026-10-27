<div>
    <!-- Notifikasi Sukses -->
    @if ($pesanSukses)
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ $pesanSukses }}</span>
            </div>
            <button wire:click="$set('pesanSukses', null)" class="text-emerald-700 hover:text-emerald-900 cursor-pointer font-bold text-xs">✕</button>
        </div>
    @endif

    <!-- Kartu Statistik & Tindakan Otomatis -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-5 rounded-2xl border border-garis shadow-xs">
            <div class="text-xs font-semibold text-tinta/60 uppercase tracking-wider">Total Agenda Rapat</div>
            <div class="text-2xl font-bold text-tinta mt-1">{{ $totalRapat }} <span class="text-xs font-normal text-tinta/50">rapat</span></div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-garis shadow-xs">
            <div class="text-xs font-semibold text-tinta/60 uppercase tracking-wider">Foto Masih Aktif</div>
            <div class="text-2xl font-bold text-amber-600 mt-1">{{ $totalMenunggu }} <span class="text-xs font-normal text-tinta/50">rapat</span></div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-garis shadow-xs">
            <div class="text-xs font-semibold text-tinta/60 uppercase tracking-wider">Foto Telah Dibersihkan</div>
            <div class="text-2xl font-bold text-daun mt-1">{{ $totalDibersihkan }} <span class="text-xs font-normal text-tinta/50">rapat</span></div>
        </div>
    </div>

    <!-- Kotak Edukasi Privasi UU PDP & Tombol Eksekusi Cepat -->
    <div class="bg-white rounded-2xl border border-garis p-6 shadow-xs mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <div class="space-y-1 max-w-2xl">
            <div class="flex items-center gap-2 text-xs font-bold text-emerald-800 uppercase tracking-wider">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Kebijakan Retensi Otomatis Harian</span>
            </div>
            <h3 class="text-base font-bold text-tinta">Penghapusan File Foto Wajah Kehadiran</h3>
            <p class="text-xs text-tinta/70 leading-relaxed">
                Sesuai prinsip pembatasan penyimpanan (storage limitation) UU PDP, foto wajah peserta rapat hanya disimpan selama masa retensi (default 90 hari). Scheduler berjalan otomatis setiap hari pukul 01:00 WITA. Anda juga dapat memicu pembersihan segera di bawah ini.
            </p>
        </div>

        <div class="shrink-0 flex flex-wrap items-center gap-3">
            <button wire:click="bersihkanSemuaKadaluarsa" 
                    type="button" 
                    data-confirm="Jalankan pembersihan foto untuk semua rapat yang telah melewati batas hari retensi?"
                    data-confirm-danger
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-daun hover:bg-daun-tua text-white text-xs sm:text-sm font-semibold shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span>Jalankan Pembersihan Otomatis Sekarang</span>
            </button>
        </div>
    </div>

    <!-- Filter & Pencarian Tabel -->
    <div class="bg-white rounded-2xl border border-garis shadow-xs p-4 sm:p-5 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-tinta/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="cari" 
                       type="text" 
                       placeholder="Cari judul rapat atau kode..." 
                       class="w-full pl-10 pr-4 py-2 bg-kertas/50 border border-garis rounded-xl text-xs sm:text-sm text-tinta placeholder-tinta/40 focus:outline-none focus:border-daun focus:ring-1 focus:ring-daun transition" />
            </div>

            <div class="flex items-center gap-2">
                <select wire:model.live="filterStatus" 
                        class="py-2 px-3 bg-kertas/50 border border-garis rounded-xl text-xs sm:text-sm text-tinta focus:outline-none focus:border-daun">
                    <option value="">Semua Status Foto</option>
                    <option value="aktif">Foto Belum Dibersihkan</option>
                    <option value="dibersihkan">Foto Sudah Dibersihkan</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Retensi Rapat -->
    <div class="bg-white rounded-2xl border border-garis shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-kertas/80 border-b border-garis text-xs font-bold text-tinta/70 uppercase tracking-wider">
                        <th class="py-3.5 px-4 sm:px-6">Kode & Agenda Rapat</th>
                        <th class="py-3.5 px-4">Tanggal Pelaksanaan</th>
                        <th class="py-3.5 px-4">Masa Retensi</th>
                        <th class="py-3.5 px-4">Batas Akhir Retensi</th>
                        <th class="py-3.5 px-4">Status File Foto</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-garis/60">
                    @forelse ($daftarRapat as $rapat)
                        @php
                            $batasRetensi = $rapat->tanggal ? $rapat->tanggal->copy()->addDays($rapat->retensi_hari) : null;
                            $sudahLewat = $batasRetensi ? $batasRetensi->isPast() : false;
                            $sudahDibersihkan = $rapat->foto_dihapus_pada !== null;
                        @endphp
                        <tr class="hover:bg-kertas/30 transition">
                            <!-- Kode & Judul -->
                            <td class="py-4 px-4 sm:px-6">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded font-mono text-[11px] font-bold bg-daun/10 text-daun border border-daun/20">
                                        {{ $rapat->kode }}
                                    </span>
                                    <a href="{{ route('rapat.detail', $rapat->id) }}" class="font-bold text-tinta hover:text-daun transition">
                                        {{ $rapat->judul }}
                                    </a>
                                </div>
                            </td>

                            <!-- Tanggal -->
                            <td class="py-4 px-4 text-xs text-tinta/70 whitespace-nowrap">
                                {{ $rapat->tanggal?->translatedFormat('d M Y') ?: '-' }}
                            </td>

                            <!-- Retensi Hari (Inline Edit) -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                @if ($editRapatId === $rapat->id)
                                    <div class="flex items-center gap-1.5">
                                        <input wire:model="editRetensiHari" 
                                               type="number" 
                                               min="1" 
                                               max="365" 
                                               class="w-16 px-2 py-1 text-xs bg-white border border-daun rounded-lg text-center font-bold" />
                                        <span class="text-xs text-tinta/60">hari</span>
                                        <button wire:click="simpanRetensi('{{ $rapat->id }}')" 
                                                class="p-1 rounded bg-daun text-white text-xs hover:bg-daun-tua cursor-pointer" title="Simpan">✓</button>
                                        <button wire:click="batalEditRetensi" 
                                                class="p-1 rounded bg-gray-200 text-gray-700 text-xs hover:bg-gray-300 cursor-pointer" title="Batal">✕</button>
                                    </div>
                                @else
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-tinta">{{ $rapat->retensi_hari }}</span>
                                        <span class="text-xs text-tinta/50">hari</span>
                                        <button wire:click="mulaiEditRetensi('{{ $rapat->id }}', {{ $rapat->retensi_hari }})" 
                                                type="button" 
                                                class="text-tinta/40 hover:text-daun transition cursor-pointer p-0.5" 
                                                title="Ubah Masa Retensi">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                    </div>
                                @endif
                            </td>

                            <!-- Batas Akhir -->
                            <td class="py-4 px-4 text-xs whitespace-nowrap">
                                @if ($batasRetensi)
                                    <span class="{{ $sudahLewat ? 'text-rose-600 font-semibold' : 'text-tinta/70' }}">
                                        {{ $batasRetensi->translatedFormat('d M Y') }}
                                    </span>
                                    @if ($sudahLewat && !$sudahDibersihkan)
                                        <span class="block text-[10px] text-rose-600 font-medium">Lewat {{ $batasRetensi->diffForHumans() }}</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>

                            <!-- Status File Foto -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                @if ($sudahDibersihkan)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Dibersihkan ({{ $rapat->foto_dihapus_pada?->translatedFormat('d/m/y') }})</span>
                                    </span>
                                @elseif ($sudahLewat)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 animate-pulse">
                                        <span>Siap Dibersihkan (Kedaluwarsa)</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                        <span>{{ $rapat->foto_count }} foto tersimpan</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-4 sm:px-6 text-right whitespace-nowrap">
                                @if (!$sudahDibersihkan)
                                    <button wire:click="konfirmasiBersihkan('{{ $rapat->id }}')" 
                                            type="button" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <span>Bersihkan Foto</span>
                                    </button>
                                @else
                                    <span class="text-xs text-tinta/40 italic">Permanen</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 px-6 text-center text-tinta/50 italic">
                                Tidak ada data rapat yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($daftarRapat->hasPages())
            <div class="p-4 border-t border-garis">
                {{ $daftarRapat->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Konfirmasi Pembersihan -->
    @if ($bukaModalKonfirmasi)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl border border-garis shadow-xl max-w-md w-full p-6 space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>

                <div class="text-center space-y-1">
                    <h3 class="text-base font-bold text-tinta">Konfirmasi Pembersihan Foto</h3>
                    <p class="text-xs text-tinta/70">
                        Apakah Anda yakin ingin menghapus seluruh file foto fisik kehadiran untuk rapat:
                        <strong class="text-tinta block mt-1">{{ $rapatTargetJudul }}</strong>?
                    </p>
                    <p class="text-[11px] text-rose-600 font-semibold mt-2">
                        Tindakan ini tidak dapat dibatalkan (permanen sesuai UU PDP).
                    </p>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2.5">
                    <button wire:click="$set('bukaModalKonfirmasi', false)" 
                            type="button" 
                            class="px-4 py-2 rounded-xl bg-kertas hover:bg-garis/50 text-xs font-semibold text-tinta transition cursor-pointer">
                        Batal
                    </button>
                    <button wire:click="eksekusiBersihkanFoto" 
                            type="button" 
                            class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-xs font-semibold text-white shadow-xs transition cursor-pointer">
                        Ya, Hapus Foto Permanen
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
