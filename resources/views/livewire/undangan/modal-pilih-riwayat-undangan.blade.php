<div>
    @if ($tampil && $rapat)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto bg-tinta/60 backdrop-blur-xs">
            <div class="relative w-full max-w-3xl bg-white rounded-3xl shadow-2xl border border-garis overflow-hidden flex flex-col max-h-[90vh]">
                <!-- Header Modal -->
                <div class="bg-daun-tua px-6 py-5 text-white flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center border border-white/20">
                            <svg class="w-5 h-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold leading-tight">Salin dari Bank Riwayat Undangan</h3>
                            <p class="text-xs text-emerald-200/80 mt-0.5">Pilih tokoh / warga unik dari rapat-rapat sebelumnya</p>
                        </div>
                    </div>
                    <button wire:click="tutup" type="button" class="text-white/70 hover:text-white cursor-pointer text-xl font-bold p-1">
                        ✕
                    </button>
                </div>

                <!-- Konten Modal -->
                <div class="p-6 overflow-y-auto flex-1 space-y-4">
                    <!-- Search & Kontrol Pilih -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-tinta/40">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input wire:model.live.debounce.250ms="cari" 
                                   type="text" 
                                   placeholder="Cari nama, jabatan, atau instansi di bank riwayat..." 
                                   class="w-full pl-9 pr-3.5 py-2 bg-kertas/60 border border-garis rounded-xl text-xs text-tinta placeholder-tinta/40 focus:outline-none focus:border-daun focus:ring-1 focus:ring-daun transition" />
                        </div>

                        @if ($riwayat->isNotEmpty())
                            <button wire:click="toggleSemua" 
                                    type="button" 
                                    class="px-3 py-2 rounded-xl border border-garis hover:border-daun text-xs font-semibold text-tinta/80 hover:text-daun transition cursor-pointer whitespace-nowrap">
                                {{ count($terpilih) === count($riwayat) ? 'Batalkan Semua' : 'Pilih Semua (' . count($riwayat) . ')' }}
                            </button>
                        @endif
                    </div>

                    @if ($pesanInfo)
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                            {{ $pesanInfo }}
                        </div>
                    @endif

                    <!-- Daftar Riwayat -->
                    <div class="border border-garis rounded-2xl overflow-hidden max-h-80 overflow-y-auto divide-y divide-garis/60">
                        @forelse ($riwayat as $idx => $item)
                            @php
                                $isDipilih = in_array($idx, $terpilih, true);
                            @endphp
                            <label class="flex items-center gap-3 p-3 hover:bg-kertas/50 transition cursor-pointer select-none {{ $isDipilih ? 'bg-emerald-50/50' : '' }}">
                                <input type="checkbox" 
                                       wire:model.live="terpilih" 
                                       value="{{ $idx }}" 
                                       class="w-4 h-4 rounded text-daun focus:ring-daun border-garis cursor-pointer" />
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-xs text-tinta truncate">{{ $item->nama }}</div>
                                    <div class="text-[11px] text-tinta/60 truncate">
                                        {{ $item->jabatan ?: '-' }} · {{ $item->instansi ?: '-' }} @if($item->hp) · <span class="font-mono text-tinta/50">{{ $item->hp }}</span> @endif
                                    </div>
                                </div>
                            </label>
                        @empty
                            <div class="p-8 text-center text-tinta/50 space-y-2">
                                <svg class="w-10 h-10 mx-auto text-tinta/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <p class="text-xs font-medium text-tinta/70">Belum ada riwayat undangan yang tersimpan</p>
                                <p class="text-[11px] text-tinta/40">Riwayat otomatis terkumpul seiring Anda membuat agenda dan mengimpor undangan di sistem.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="px-6 py-4 bg-kertas/40 border-t border-garis flex items-center justify-between gap-3 shrink-0">
                    <div class="text-xs text-tinta/60">
                        <span class="font-semibold text-daun">{{ count($terpilih) }}</span> nama dipilih
                    </div>
                    <div class="flex items-center gap-2">
                        <button wire:click="tutup" type="button" class="px-4 py-2 rounded-xl border border-garis text-xs font-medium text-tinta/80 hover:bg-white transition cursor-pointer">
                            Batal
                        </button>
                        <button wire:click="salinTerpilih" 
                                wire:loading.attr="disabled"
                                type="button" 
                                @if (empty($terpilih)) disabled @endif
                                class="px-5 py-2 rounded-xl bg-daun hover:bg-daun-tua text-white text-xs font-semibold shadow-sm shadow-daun/20 focus:outline-none focus:ring-2 focus:ring-daun focus:ring-offset-2 transition cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                            <span wire:loading.remove>Salin {{ count($terpilih) }} Undangan</span>
                            <span wire:loading>Menyalin...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
