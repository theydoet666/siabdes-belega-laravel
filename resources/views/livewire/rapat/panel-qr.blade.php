<div>
    @if ($tampil && $rapat)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto bg-tinta/60 backdrop-blur-xs">
            <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-garis overflow-hidden">
                <!-- Header Modal -->
                <div class="bg-daun-tua px-6 py-5 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center border border-white/20">
                            <svg class="w-5 h-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold leading-tight">QR Absensi Mandiri</h3>
                            <p class="text-xs text-emerald-200/80 mt-0.5">Kode Rapat: <span class="font-mono font-bold tracking-wider text-amber-300">{{ $rapat->kode }}</span></p>
                        </div>
                    </div>
                    <button wire:click="tutup" type="button" class="text-white/70 hover:text-white cursor-pointer text-xl font-bold p-1">
                        ✕
                    </button>
                </div>

                <!-- Konten QR -->
                <div class="p-6 text-center">
                    <div class="mb-4">
                        <h4 class="font-bold text-base text-tinta line-clamp-1">{{ $rapat->judul }}</h4>
                        <p class="text-xs text-tinta/60 mt-1">Pindai QR ini melalui kamera smartphone peserta untuk registrasi mandiri</p>
                    </div>

                    <!-- QR Code Container -->
                    <div class="inline-block p-4 bg-white rounded-2xl border-2 border-dashed border-garis shadow-inner mb-5">
                        <div class="w-60 h-60 flex items-center justify-center mx-auto">
                            {!! $qrSvg !!}
                        </div>
                    </div>

                    <!-- Tautan URL & Salin -->
                    <div x-data="{ disalin: false }" class="space-y-3">
                        <div class="flex items-center gap-2 p-2 bg-kertas rounded-xl border border-garis">
                            <input type="text" 
                                   readonly 
                                   value="{{ $urlRegistrasi }}" 
                                   id="inputUrlRegistrasi"
                                   class="bg-transparent border-0 text-xs font-mono text-tinta/80 flex-1 px-2 focus:outline-none select-all" />
                            <button type="button" 
                                    @click="navigator.clipboard.writeText('{{ $urlRegistrasi }}'); disalin = true; setTimeout(() => disalin = false, 2500)"
                                    class="px-3 py-1.5 rounded-lg bg-daun hover:bg-daun-tua text-white text-xs font-semibold shrink-0 transition cursor-pointer">
                                <span x-show="!disalin">Salin Tautan</span>
                                <span x-show="disalin" x-cloak class="flex items-center gap-1 text-emerald-200">
                                    ✓ Tersalin
                                </span>
                            </button>
                        </div>

                        <div class="flex items-center justify-center gap-2 pt-2">
                            <a href="{{ $urlRegistrasi }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-kertas hover:bg-garis/50 border border-garis text-xs font-medium text-tinta transition">
                                <svg class="w-3.5 h-3.5 text-daun" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                <span>Buka Halaman Registrasi</span>
                            </a>
                            <button type="button" wire:click="tutup" class="px-4 py-2 rounded-xl border border-garis text-xs font-medium text-tinta/70 hover:text-tinta transition cursor-pointer">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
