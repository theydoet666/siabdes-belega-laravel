<div>
    @if ($tampil && $rapatAsal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto bg-tinta/60 backdrop-blur-xs">
            <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-garis overflow-hidden">
                <!-- Header Modal -->
                <div class="bg-daun-tua px-6 py-5 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center border border-white/20">
                            <svg class="w-5 h-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold leading-tight">Duplikasi Rapat</h3>
                            <p class="text-xs text-emerald-200/80 mt-0.5">Buat agenda baru berdasarkan rapat sebelumnya</p>
                        </div>
                    </div>
                    <button wire:click="tutup" type="button" class="text-white/70 hover:text-white cursor-pointer text-xl font-bold p-1">
                        ✕
                    </button>
                </div>

                <!-- Form Duplikasi -->
                <form wire:submit="duplikasi" class="p-6 space-y-4">
                    <!-- Info Rapat Asal -->
                    <div class="p-3.5 bg-kertas rounded-xl border border-garis text-xs text-tinta/70">
                        <span class="font-semibold text-tinta">Rapat Asal:</span>
                        <div class="font-medium text-tinta mt-0.5">{{ $rapatAsal->judul }}</div>
                        <div class="text-[11px] text-tinta/50 mt-1">Kode: {{ $rapatAsal->kode }} · Tanggal: {{ $rapatAsal->tanggal?->translatedFormat('d M Y') }}</div>
                    </div>

                    <!-- Judul Rapat Baru -->
                    <div>
                        <label for="judulBaru" class="block text-xs font-semibold text-tinta mb-1.5">
                            Judul Rapat Baru <span class="text-rose-600">*</span>
                        </label>
                        <input wire:model="judulBaru" 
                               type="text" 
                               id="judulBaru" 
                               required 
                               class="w-full px-3.5 py-2.5 bg-kertas/50 border @error('judulBaru') border-rose-300 focus:border-rose-500 @else border-garis focus:border-daun @enderror rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                        @error('judulBaru')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Opsi Salin Undangan -->
                    <div class="p-3.5 bg-emerald-50/50 rounded-xl border border-emerald-100">
                        <label class="flex items-start gap-3 cursor-pointer select-none">
                            <input wire:model="salinUndangan" 
                                   type="checkbox" 
                                   class="w-4 h-4 mt-0.5 rounded text-daun focus:ring-daun border-garis transition cursor-pointer" />
                            <div>
                                <span class="text-xs font-semibold text-tinta block">Salin Seluruh Daftar Undangan</span>
                                <span class="text-[11px] text-tinta/60 block mt-0.5">
                                    Menyalin {{ $totalUndangan }} nama undangan dari rapat asal ke rapat baru. Data kehadiran tanda tangan tidak akan disalin (dimulai dari nol).
                                </span>
                            </div>
                        </label>
                    </div>

                    <!-- Catatan Konfigurasi -->
                    <p class="text-[11px] text-tinta/50">
                        * Kode rapat 4 karakter baru akan dibuat secara otomatis dan status rapat baru diatur sebagai <strong>Draft</strong>.
                    </p>

                    <!-- Tombol Aksi Modal -->
                    <div class="pt-3 flex items-center justify-end gap-3 border-t border-garis">
                        <button wire:click="tutup" 
                                type="button" 
                                class="px-4 py-2.5 rounded-xl border border-garis text-sm font-medium text-tinta/80 hover:bg-kertas/60 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                wire:loading.attr="disabled"
                                class="px-5 py-2.5 rounded-xl bg-daun hover:bg-daun-tua text-white text-sm font-semibold shadow-sm shadow-daun/20 focus:outline-none focus:ring-2 focus:ring-daun focus:ring-offset-2 transition cursor-pointer disabled:opacity-50">
                            <span wire:loading.remove>Duplikasi Sekarang</span>
                            <span wire:loading>Menduplikasi...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
