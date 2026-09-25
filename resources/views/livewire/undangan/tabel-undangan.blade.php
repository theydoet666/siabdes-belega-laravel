<div>
    <!-- Notifikasi Sukses / Error -->
    @if ($pesanSukses)
        <div class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ $pesanSukses }}</span>
            </div>
            <button wire:click="$set('pesanSukses', null)" class="text-emerald-700 hover:text-emerald-900 cursor-pointer font-bold text-xs">✕</button>
        </div>
    @endif

    <!-- Header Panel Undangan & Tombol Aksi -->
    <div class="bg-white rounded-3xl border border-garis shadow-sm p-5 sm:p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-tinta">Daftar Undangan Rapat</h3>
                <p class="text-xs text-tinta/60 mt-0.5">Kelola nama tokoh, instansi, dan peserta yang berhak melakukan absensi</p>
            </div>

            <!-- Tombol Aksi Undangan -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Tambah Manual -->
                <button wire:click="$dispatch('buka-modal-tambah-undangan', { rapatId: '{{ $rapatId }}' })" 
                        type="button" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-daun hover:bg-daun-tua text-white text-xs font-semibold shadow-sm transition cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Manual</span>
                </button>

                <!-- Impor CSV / Excel -->
                <button wire:click="$dispatch('buka-modal-impor-csv', { rapatId: '{{ $rapatId }}' })" 
                        type="button" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-kertas border border-garis text-xs font-semibold text-tinta transition cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-daun" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <span>Impor CSV / Excel</span>
                </button>

                <!-- Salin dari Bank Riwayat -->
                <button wire:click="$dispatch('buka-modal-riwayat', { rapatId: '{{ $rapatId }}' })" 
                        type="button" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-kertas border border-garis text-xs font-semibold text-tinta transition cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-kuning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span>Salin Riwayat</span>
                </button>
            </div>
        </div>

        <!-- Filter & Pencarian -->
        <div class="mt-5 pt-4 border-t border-garis flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-tinta/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="cari" 
                       type="text" 
                       placeholder="Cari nama undangan, jabatan, instansi, atau HP..." 
                       class="w-full pl-10 pr-4 py-2 bg-kertas/50 border border-garis rounded-xl text-xs text-tinta placeholder-tinta/40 focus:outline-none focus:border-daun focus:ring-1 focus:ring-daun transition" />
            </div>

            <div class="flex items-center gap-2">
                <select wire:model.live="filterSumber" 
                        class="py-2 px-3 bg-kertas/50 border border-garis rounded-xl text-xs text-tinta focus:outline-none focus:border-daun focus:ring-1 focus:ring-daun transition">
                    <option value="">Semua Sumber</option>
                    <option value="import">Daftar Resmi (Import)</option>
                    <option value="tambahan">Undangan Tambahan</option>
                </select>

                <select wire:model.live="filterKehadiran" 
                        class="py-2 px-3 bg-kertas/50 border border-garis rounded-xl text-xs text-tinta focus:outline-none focus:border-daun focus:ring-1 focus:ring-daun transition">
                    <option value="">Status Kehadiran</option>
                    <option value="hadir">Sudah Hadir</option>
                    <option value="belum">Belum Hadir</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Tabel Undangan -->
    <div class="bg-white rounded-3xl border border-garis shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-kertas/80 border-b border-garis text-[11px] font-bold text-tinta/70 uppercase tracking-wider">
                        <th class="py-3 px-4 sm:px-6 w-12 text-center">#</th>
                        <th class="py-3 px-4">Nama Undangan</th>
                        <th class="py-3 px-4">Jabatan & Instansi</th>
                        <th class="py-3 px-4">No. HP</th>
                        <th class="py-3 px-4">Sumber</th>
                        <th class="py-3 px-4">Kehadiran</th>
                        <th class="py-3 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-garis/60">
                    @forelse ($daftarUndangan as $index => $item)
                        @php
                            $sudahHadir = $item->kehadiran->isNotEmpty();
                        @endphp
                        <tr class="hover:bg-kertas/30 transition">
                            <!-- Nomor Urut -->
                            <td class="py-3.5 px-4 sm:px-6 text-center text-tinta/50 font-mono text-xs">
                                {{ $daftarUndangan->firstItem() + $index }}
                            </td>

                            <!-- Nama -->
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-tinta text-xs sm:text-sm">{{ $item->nama }}</div>
                            </td>

                            <!-- Jabatan & Instansi -->
                            <td class="py-3.5 px-4 text-tinta/70">
                                <div class="font-medium text-tinta/80">{{ $item->jabatan ?: '-' }}</div>
                                <div class="text-[11px] text-tinta/50">{{ $item->instansi ?: '-' }}</div>
                            </td>

                            <!-- HP -->
                            <td class="py-3.5 px-4 font-mono text-tinta/60">
                                {{ $item->hp ?: '-' }}
                            </td>

                            <!-- Sumber -->
                            <td class="py-3.5 px-4">
                                @if ($item->sumber === \App\Enums\SumberUndangan::IMPORT)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-daun border border-emerald-200">
                                        Resmi
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-kuning border border-amber-200">
                                        Tambahan
                                    </span>
                                @endif
                            </td>

                            <!-- Status Kehadiran -->
                            <td class="py-3.5 px-4">
                                @if ($sudahHadir)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        Hadir
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 text-gray-600">
                                        Belum
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 sm:px-6 text-right">
                                <button wire:click="hapusUndangan('{{ $item->id }}')" 
                                        wire:confirm="Yakin ingin menghapus nama '{{ $item->nama }}' dari daftar undangan rapat ini?"
                                        type="button" 
                                        class="p-1 rounded-lg text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                                        title="Hapus Undangan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 px-4 text-center text-tinta/50">
                                <div class="max-w-sm mx-auto space-y-2">
                                    <svg class="w-10 h-10 mx-auto text-tinta/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <p class="font-medium text-xs text-tinta/70">Belum ada daftar undangan pada rapat ini</p>
                                    <p class="text-[11px] text-tinta/40">Gunakan tombol di atas untuk menambah undangan manual, mengimpor berkas Excel/CSV, atau menyalin dari riwayat rapat lama.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginasi -->
        @if ($daftarUndangan->hasPages())
            <div class="px-6 py-4 border-t border-garis bg-kertas/30">
                {{ $daftarUndangan->links() }}
            </div>
        @endif
    </div>

    <!-- Modals Terintegrasi -->
    <livewire:undangan.impor-csv />
    <livewire:undangan.modal-pilih-riwayat-undangan />
    <livewire:undangan.form-undangan />
</div>
