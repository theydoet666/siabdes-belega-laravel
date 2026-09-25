@php
    $pengaturanSistem = \App\Models\PengaturanSistem::first();
    $namaSistem = $pengaturanSistem->nama_sistem ?? 'SIABDES Belega';
    $subjudulSistem = $pengaturanSistem->subjudul_sistem ?? 'Sistem Absensi Rapat Kantor Desa';
@endphp

<footer class="bg-white border-t border-garis mt-auto py-5 text-center text-xs text-tinta/70 w-full">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3 text-xs text-tinta/60">
            <span>© {{ date('Y') }} {{ $namaSistem }} · {{ $subjudulSistem }}</span>
            <span>·</span>
            <a href="{{ route('privasi') }}" target="_blank" class="text-daun hover:underline font-semibold transition">
                Kebijakan Privasi (UU PDP)
            </a>
        </div>
    </div>
</footer>
