<div>
    @if ($tampil && $rapat)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto bg-tinta/60 backdrop-blur-xs">
            <div class="relative w-full max-w-4xl bg-white rounded-3xl shadow-2xl border border-garis overflow-hidden flex flex-col max-h-[90vh]">
                <!-- Header Modal -->
                <div class="bg-daun-tua px-6 py-5 text-white flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center border border-white/20">
                            <svg class="w-5 h-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold leading-tight">Impor Undangan (CSV / Excel)</h3>
                            <p class="text-xs text-emerald-200/80 mt-0.5">Rapat: <span class="font-semibold text-white">{{ $rapat->judul }}</span></p>
                        </div>
                    </div>
                    <button wire:click="tutup" type="button" class="text-white/70 hover:text-white cursor-pointer text-xl font-bold p-1">
                        ✕
                    </button>
                </div>

                <!-- Body Modal -->
                <div class="p-6 overflow-y-auto flex-1 space-y-5">
                    <!-- Area Upload Berkas -->
                    <div class="p-5 bg-kertas/60 rounded-2xl border-2 border-dashed border-garis text-center hover:border-daun transition">
                        <input type="file" 
                               wire:model="file" 
                               id="fileInput" 
                               accept=".csv, .xlsx, .xls" 
                               class="hidden" />
                        <label for="fileInput" class="cursor-pointer block space-y-2">
                            <div class="w-12 h-12 mx-auto rounded-2xl bg-emerald-50 text-daun border border-emerald-200 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="text-xs font-semibold text-tinta">
                                Klik untuk memilih berkas <span class="text-daun">CSV atau Excel (.xlsx, .xls)</span>
                            </div>
                            <p class="text-[11px] text-tinta/50">
                                Format kolom: Nama (wajib), Jabatan, Instansi/Banjar, No HP (opsional). Baris header dideteksi otomatis.
                            </p>
                        </label>

                        <!-- Loading Indicator Upload -->
                        <div wire:loading wire:target="file" class="mt-3 text-xs font-semibold text-daun flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-daun" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Membaca dan memvalidasi berkas...</span>
                        </div>
                    </div>

                    @error('file')
                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2">
                            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror

                    @if ($pesanError)
                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                            {{ $pesanError }}
                        </div>
                    @endif

                    <!-- Hasil Pratinjau Berkas -->
                    @if ($previewData)
                        <div class="space-y-4">
                            <!-- Metrik Ringkas -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <div class="bg-kertas p-3 rounded-xl border border-garis text-center">
                                    <span class="text-[10px] font-semibold text-tinta/60 uppercase">Total Baris</span>
                                    <div class="text-lg font-bold text-tinta">{{ $previewData['total'] }}</div>
                                </div>
                                <div class="bg-emerald-50 p-3 rounded-xl border border-emerald-200 text-center">
                                    <span class="text-[10px] font-semibold text-emerald-800 uppercase">Siap Diimpor</span>
                                    <div class="text-lg font-bold text-daun">{{ $previewData['total_valid'] }}</div>
                                </div>
                                <div class="bg-amber-50 p-3 rounded-xl border border-amber-200 text-center">
                                    <span class="text-[10px] font-semibold text-kuning uppercase">Duplikat</span>
                                    <div class="text-lg font-bold text-amber-700">{{ $previewData['total_duplikat'] }}</div>
                                </div>
                                <div class="bg-rose-50 p-3 rounded-xl border border-rose-200 text-center">
                                    <span class="text-[10px] font-semibold text-rose-800 uppercase">Tidak Valid</span>
                                    <div class="text-lg font-bold text-rose-700">{{ $previewData['total_invalid'] }}</div>
                                </div>
                            </div>

                            <!-- Opsi Duplikat -->
                            @if ($previewData['total_duplikat'] > 0)
                                <div class="p-3 bg-amber-50/70 rounded-xl border border-amber-200 flex items-center justify-between">
                                    <label class="flex items-center gap-2 text-xs font-semibold text-amber-900 cursor-pointer select-none">
                                        <input wire:model="abaikanDuplikat" type="checkbox" class="w-4 h-4 rounded text-daun focus:ring-daun border-garis cursor-pointer" />
                                        <span>Abaikan {{ $previewData['total_duplikat'] }} baris duplikat (hanya impor data baru yang unik)</span>
                                    </label>
                                </div>
                            @endif

                            <!-- Tabel Preview -->
                            <div class="border border-garis rounded-2xl overflow-hidden max-h-64 overflow-y-auto">
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead class="bg-kertas/80 sticky top-0 border-b border-garis text-tinta/70 font-bold uppercase tracking-wider">
                                        <tr>
                                            <th class="py-2.5 px-3">#</th>
                                            <th class="py-2.5 px-3">Nama</th>
                                            <th class="py-2.5 px-3">Jabatan</th>
                                            <th class="py-2.5 px-3">Instansi</th>
                                            <th class="py-2.5 px-3">No. HP</th>
                                            <th class="py-2.5 px-3">Status Validasi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-garis/60">
                                        @foreach ($previewData['baris'] as $b)
                                            <tr class="hover:bg-kertas/40 transition {{ $b['status'] === 'duplikat' ? 'bg-amber-50/40' : ($b['status'] === 'invalid' ? 'bg-rose-50/40' : '') }}">
                                                <td class="py-2 px-3 text-tinta/40 font-mono">{{ $b['nomor'] }}</td>
                                                <td class="py-2 px-3 font-semibold text-tinta">{{ $b['nama'] ?: '-' }}</td>
                                                <td class="py-2 px-3 text-tinta/70">{{ $b['jabatan'] ?: '-' }}</td>
                                                <td class="py-2 px-3 text-tinta/70">{{ $b['instansi'] ?: '-' }}</td>
                                                <td class="py-2 px-3 text-tinta/70 font-mono">{{ $b['hp'] ?: '-' }}</td>
                                                <td class="py-2 px-3">
                                                    @if ($b['status'] === 'valid')
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                                            ✓ Siap
                                                        </span>
                                                    @elseif ($b['status'] === 'duplikat')
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-900" title="{{ $b['pesan'] }}">
                                                            ⚠ Duplikat
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800" title="{{ $b['pesan'] }}">
                                                            ✕ Invalid
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Footer Aksi Modal -->
                <div class="px-6 py-4 bg-kertas/40 border-t border-garis flex items-center justify-end gap-3 shrink-0">
                    <button wire:click="tutup" type="button" class="px-4 py-2.5 rounded-xl border border-garis text-xs font-medium text-tinta/80 hover:bg-white transition cursor-pointer">
                        Batal
                    </button>
                    @if ($previewData)
                        <button wire:click="eksekusiImpor" 
                                wire:loading.attr="disabled"
                                type="button" 
                                class="px-5 py-2.5 rounded-xl bg-daun hover:bg-daun-tua text-white text-xs font-semibold shadow-sm shadow-daun/20 focus:outline-none focus:ring-2 focus:ring-daun focus:ring-offset-2 transition cursor-pointer disabled:opacity-50">
                            <span wire:loading.remove>Impor {{ $abaikanDuplikat ? $previewData['total_valid'] : ($previewData['total_valid'] + $previewData['total_duplikat']) }} Undangan</span>
                            <span wire:loading>Menyimpan ke Database...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
