<!-- Indikator Status Jaringan Kiosk (Online / Offline + Antrean Pending) -->
<div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold backdrop-blur-sm border transition"
     :class="isOnline ? 'bg-emerald-500/15 text-emerald-200 border-emerald-400/30' : 'bg-amber-500/20 text-amber-200 border-amber-400/40'">
    <!-- Status Dot -->
    <span class="relative flex h-2.5 w-2.5">
        <span x-show="isOnline" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-2.5 w-2.5" :class="isOnline ? 'bg-emerald-400' : 'bg-amber-400'"></span>
    </span>

    <!-- Status Text -->
    <span x-show="isOnline">Online</span>
    <span x-show="!isOnline" x-cloak>Mode Offline</span>

    <!-- Antrean Badge jika ada pending -->
    <template x-if="jumlahAntreanPending > 0">
        <span class="px-1.5 py-0.2 rounded-full bg-amber-400 text-slate-950 font-bold text-[10px] ml-1"
              x-text="jumlahAntreanPending + ' antrean'">
        </span>
    </template>
</div>
