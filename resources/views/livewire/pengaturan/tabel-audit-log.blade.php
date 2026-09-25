<div>
    <!-- Header Ringkas & Info Log -->
    <div class="bg-white rounded-2xl border border-garis shadow-xs p-6 mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-pena uppercase tracking-wider mb-1">
                <svg class="w-4 h-4 text-pena" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Audit Trail Keamanan & Kepatuhan</span>
            </div>
            <h2 class="text-base font-bold text-tinta">Jejak Rekam Aktivitas & Perubahan Sistem</h2>
            <p class="text-xs text-tinta/60 mt-0.5">
                Merekam setiap aktivitas administratif krusial: pembuatan/penghapusan akun, modifikasi rapat, impor undangan, dan retensi foto.
            </p>
        </div>

        @if ($cari || $filterTabel || $tanggalMulai || $tanggalSelesai)
            <button wire:click="resetFilter" 
                    type="button" 
                    class="px-3.5 py-1.5 rounded-xl border border-garis hover:border-rose-300 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition cursor-pointer">
                Reset Semua Filter
            </button>
        @endif
    </div>

    <!-- Filter & Pencarian -->
    <div class="bg-white rounded-2xl border border-garis shadow-xs p-5 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Pencarian Teks -->
            <div>
                <label class="block text-xs font-semibold text-tinta mb-1.5">Cari Log / Aktor</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-tinta/40">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="cari" 
                           type="text" 
                           placeholder="Cari aksi, nama, ID..." 
                           class="w-full pl-9 pr-3 py-2 bg-kertas/50 border border-garis rounded-xl text-xs text-tinta placeholder-tinta/40 focus:outline-none focus:border-daun" />
                </div>
            </div>

            <!-- Filter Tabel Target -->
            <div>
                <label class="block text-xs font-semibold text-tinta mb-1.5">Tabel Target</label>
                <select wire:model.live="filterTabel" 
                        class="w-full py-2 px-3 bg-kertas/50 border border-garis rounded-xl text-xs text-tinta focus:outline-none focus:border-daun">
                    <option value="">Semua Tabel Target</option>
                    @foreach ($daftarTabelUnik as $tabel)
                        <option value="{{ $tabel }}">{{ ucfirst($tabel) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tanggal Mulai -->
            <div>
                <label class="block text-xs font-semibold text-tinta mb-1.5">Dari Tanggal</label>
                <input wire:model.live="tanggalMulai" 
                       type="date" 
                       class="w-full py-2 px-3 bg-kertas/50 border border-garis rounded-xl text-xs text-tinta focus:outline-none focus:border-daun" />
            </div>

            <!-- Tanggal Selesai -->
            <div>
                <label class="block text-xs font-semibold text-tinta mb-1.5">Sampai Tanggal</label>
                <input wire:model.live="tanggalSelesai" 
                       type="date" 
                       class="w-full py-2 px-3 bg-kertas/50 border border-garis rounded-xl text-xs text-tinta focus:outline-none focus:border-daun" />
            </div>
        </div>
    </div>

    <!-- Tabel Log Audit -->
    <div class="bg-white rounded-2xl border border-garis shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-kertas/80 border-b border-garis text-xs font-bold text-tinta/70 uppercase tracking-wider">
                        <th class="py-3.5 px-4 sm:px-6">Waktu (WITA)</th>
                        <th class="py-3.5 px-4">Aktor Pengguna</th>
                        <th class="py-3.5 px-4">Aksi Administratif</th>
                        <th class="py-3.5 px-4">Target Modul</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Rincian JSON</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-garis/60">
                    @forelse ($daftarLog as $log)
                        <tr class="hover:bg-kertas/30 transition">
                            <!-- Waktu -->
                            <td class="py-3.5 px-4 sm:px-6 whitespace-nowrap">
                                <div class="font-mono text-xs font-semibold text-tinta">
                                    {{ $log->dibuat_pada?->timezone('Asia/Makassar')->format('d/m/Y H:i:s') }}
                                </div>
                                <div class="text-[10px] text-tinta/40">
                                    {{ $log->dibuat_pada?->diffForHumans() }}
                                </div>
                            </td>

                            <!-- Aktor -->
                            <td class="py-3.5 px-4">
                                @if ($log->pengguna)
                                    <div class="font-bold text-tinta">{{ $log->pengguna->nama }}</div>
                                    <div class="text-[11px] text-tinta/50">{{ $log->pengguna->email }}</div>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-mono font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                        ⚙️ Sistem Otomatis / Scheduler
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 font-medium text-tinta max-w-md">
                                {{ $log->aksi }}
                            </td>

                            <!-- Target Modul -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-semibold 
                                    @if($log->tabel_target === 'rapat') bg-emerald-50 text-emerald-800 border border-emerald-200
                                    @elseif($log->tabel_target === 'undangan') bg-blue-50 text-blue-800 border border-blue-200
                                    @elseif($log->tabel_target === 'users' || $log->tabel_target === 'profil') bg-purple-50 text-purple-800 border border-purple-200
                                    @else bg-gray-100 text-gray-800 border border-gray-200
                                    @endif">
                                    {{ $log->tabel_target }}
                                </span>
                            </td>

                            <!-- Rincian -->
                            <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
                                @if ($log->rincian)
                                    <button wire:click="lihatRincian({{ $log->id }})" 
                                            type="button" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-kertas hover:bg-garis/60 text-xs font-medium text-tinta/80 transition cursor-pointer">
                                        <span>Lihat Rincian</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                @else
                                    <span class="text-xs text-tinta/40">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 px-6 text-center text-tinta/50 italic">
                                Tidak ada catatan audit log yang cocok dengan filter saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($daftarLog->hasPages())
            <div class="p-4 border-t border-garis">
                {{ $daftarLog->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Rincian JSON Log Audit -->
    @if ($bukaModalDetail && $logTerpilih)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl border border-garis shadow-xl max-w-lg w-full p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-garis pb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-daun/10 text-daun flex items-center justify-center font-bold text-xs">
                            #
                        </div>
                        <h3 class="text-sm font-bold text-tinta">Rincian Data Log Audit</h3>
                    </div>
                    <button wire:click="tutupModalDetail" class="text-tinta/50 hover:text-tinta cursor-pointer font-bold">✕</button>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-tinta/50 font-semibold block">Aksi:</span>
                        <span class="font-bold text-tinta text-sm">{{ $logTerpilih->aksi }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-tinta/50 font-semibold block">Waktu:</span>
                            <span class="text-tinta">{{ $logTerpilih->dibuat_pada?->timezone('Asia/Makassar')->format('d F Y, H:i:s') }} WITA</span>
                        </div>
                        <div>
                            <span class="text-tinta/50 font-semibold block">Aktor:</span>
                            <span class="text-tinta">{{ $logTerpilih->pengguna?->nama ?? 'Sistem Otomatis' }}</span>
                        </div>
                    </div>

                    <div>
                        <span class="text-tinta/50 font-semibold block mb-1">Payload JSON Rincian:</span>
                        <pre class="bg-slate-900 text-emerald-400 p-3.5 rounded-xl font-mono text-[11px] overflow-x-auto max-h-64 leading-relaxed">{{ json_encode($logTerpilih->rincian, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button wire:click="tutupModalDetail" 
                            type="button" 
                            class="px-4 py-2 rounded-xl bg-daun hover:bg-daun-tua text-xs font-semibold text-white shadow-xs transition cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
