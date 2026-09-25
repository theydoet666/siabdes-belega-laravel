<div>
    <!-- Header Halaman -->
    <div class="mb-8">
        <div class="flex items-center gap-3 text-xs font-semibold text-daun mb-2 uppercase tracking-wider">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>Panel Administrator</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-tinta">Pengaturan Sistem</h1>
        <p class="text-sm text-tinta/60 mt-1">Kelola akun operator, identitas instansi desa, kebijakan retensi foto privasi (UU PDP), dan rekam log audit keamanan</p>
    </div>

    <!-- Navigasi Tab Pengaturan -->
    <div class="border-b border-garis mb-6">
        <nav class="flex space-x-2 sm:space-x-4 -mb-px overflow-x-auto">
            <!-- Tab 1: Kelola Operator -->
            <button wire:click="setTab('operator')" 
                    type="button" 
                    class="py-3 px-4 font-semibold text-sm border-b-2 transition whitespace-nowrap cursor-pointer flex items-center gap-2 {{ $tabAktif === 'operator' ? 'border-daun text-daun' : 'border-transparent text-tinta/60 hover:text-tinta hover:border-garis' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>Kelola Operator</span>
            </button>

            <!-- Tab 2: Identitas Instansi Desa -->
            <button wire:click="setTab('identitas')" 
                    type="button" 
                    class="py-3 px-4 font-semibold text-sm border-b-2 transition whitespace-nowrap cursor-pointer flex items-center gap-2 {{ $tabAktif === 'identitas' ? 'border-daun text-daun' : 'border-transparent text-tinta/60 hover:text-tinta hover:border-garis' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span>Identitas & Kop Dinas</span>
            </button>

            <!-- Tab 3: Retensi Foto Privasi (UU PDP) -->
            <button wire:click="setTab('retensi')" 
                    type="button" 
                    class="py-3 px-4 font-semibold text-sm border-b-2 transition whitespace-nowrap cursor-pointer flex items-center gap-2 {{ $tabAktif === 'retensi' ? 'border-daun text-daun' : 'border-transparent text-tinta/60 hover:text-tinta hover:border-garis' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span>Retensi Privasi Foto (UU PDP)</span>
            </button>

            <!-- Tab 4: Log Audit Keamanan -->
            <button wire:click="setTab('audit')" 
                    type="button" 
                    class="py-3 px-4 font-semibold text-sm border-b-2 transition whitespace-nowrap cursor-pointer flex items-center gap-2 {{ $tabAktif === 'audit' ? 'border-daun text-daun' : 'border-transparent text-tinta/60 hover:text-tinta hover:border-garis' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Log Audit Keamanan</span>
            </button>
        </nav>
    </div>

    <!-- Konten Tab Aktif -->
    <div>
        @if ($tabAktif === 'operator')
            <livewire:pengaturan.kelola-operator :key="'pengaturan-tab-operator'" />
        @elseif ($tabAktif === 'identitas')
            <livewire:pengaturan.pengaturan-identitas :key="'pengaturan-tab-identitas'" />
        @elseif ($tabAktif === 'retensi')
            <livewire:pengaturan.pengaturan-retensi :key="'pengaturan-tab-retensi'" />
        @elseif ($tabAktif === 'audit')
            <livewire:pengaturan.tabel-audit-log :key="'pengaturan-tab-audit'" />
        @endif
    </div>
</div>
