import './bootstrap';
import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import html2canvas from 'html2canvas';

import tandaTanganPad from './alpine/tandaTanganPad';
import kameraWajah from './alpine/kameraWajah';
import kioskApp from './alpine/kioskApp';
import db from './offline/db';
import antrean from './offline/antrean';

window.html2canvas = html2canvas;

// Konfigurasi SweetAlert2 bertema SIABDES Belega
const SiabdesAlert = Swal.mixin({
    customClass: {
        popup: 'rounded-3xl border border-[#d6d9cd] shadow-2xl p-6 font-sans bg-white',
        title: 'text-lg font-bold text-[#16241d]',
        htmlContainer: 'text-xs sm:text-sm text-[#16241d]/75 leading-relaxed mt-2',
        confirmButton: 'inline-flex items-center justify-center rounded-xl px-5 py-2.5 font-semibold text-xs sm:text-sm text-white shadow-sm transition cursor-pointer mx-1.5',
        cancelButton: 'inline-flex items-center justify-center rounded-xl px-5 py-2.5 font-semibold text-xs sm:text-sm text-[#16241d]/80 bg-gray-100 hover:bg-gray-200 border border-[#d6d9cd] shadow-xs transition cursor-pointer mx-1.5',
    },
    buttonsStyling: false,
    focusCancel: true
});

window.Swal = Swal;
window.SiabdesAlert = SiabdesAlert;

// Interceptor Global untuk Konfirmasi dengan SweetAlert2 (data-confirm & data-confirm-danger)
document.addEventListener('click', (e) => {
    const trigger = e.target.closest('[data-confirm]');
    if (!trigger) return;

    if (trigger._swalConfirmed) {
        trigger._swalConfirmed = false;
        return; // Loloskan klik asli ke Livewire / Alpine
    }

    e.preventDefault();
    e.stopImmediatePropagation();

    const message = trigger.getAttribute('data-confirm');
    const isDanger = trigger.hasAttribute('data-confirm-danger') || 
                     trigger.classList.contains('text-rose-600') || 
                     trigger.classList.contains('text-rose-700') || 
                     trigger.classList.contains('bg-rose-600') || 
                     /hapus|permanen|nonaktifkan|bersihkan|delete|keluar|logout/i.test(message);

    SiabdesAlert.fire({
        title: isDanger ? 'Konfirmasi Tindakan' : 'Konfirmasi Tindakan',
        text: message,
        icon: isDanger ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonText: isDanger ? 'Ya, Lanjutkan' : 'Ya, Setuju',
        cancelButtonText: 'Batal',
        customClass: {
            popup: 'rounded-3xl border border-[#d6d9cd] shadow-2xl p-6 font-sans bg-white',
            title: 'text-lg font-bold text-[#16241d]',
            htmlContainer: 'text-xs sm:text-sm text-[#16241d]/75 leading-relaxed mt-2',
            confirmButton: `inline-flex items-center justify-center rounded-xl px-5 py-2.5 font-semibold text-xs sm:text-sm text-white shadow-sm transition cursor-pointer mx-1.5 ${isDanger ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-700 hover:bg-emerald-800'}`,
            cancelButton: 'inline-flex items-center justify-center rounded-xl px-5 py-2.5 font-semibold text-xs sm:text-sm text-[#16241d]/80 bg-gray-100 hover:bg-gray-200 border border-[#d6d9cd] shadow-xs transition cursor-pointer mx-1.5'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            trigger._swalConfirmed = true;
            trigger.click();
        }
    });
}, true);

// Listener Event Livewire untuk Notifikasi & SweetAlert
window.addEventListener('swal:alert', (event) => {
    const detail = event.detail?.[0] || event.detail || {};
    SiabdesAlert.fire({
        title: detail.title || 'Informasi',
        text: detail.message || detail.text || '',
        icon: detail.icon || 'info',
        confirmButtonText: 'Tutup',
        customClass: {
            popup: 'rounded-3xl border border-[#d6d9cd] shadow-2xl p-6 font-sans bg-white',
            title: 'text-lg font-bold text-[#16241d]',
            htmlContainer: 'text-xs sm:text-sm text-[#16241d]/75 leading-relaxed mt-2',
            confirmButton: 'inline-flex items-center justify-center rounded-xl px-5 py-2.5 font-semibold text-xs sm:text-sm text-white bg-emerald-700 hover:bg-emerald-800 shadow-sm transition cursor-pointer'
        }
    });
});

window.Alpine = Alpine;
window.DexieDb = db;
window.KioskAntrean = antrean;

// Register Alpine components
Alpine.data('tandaTanganPad', tandaTanganPad);
Alpine.data('kameraWajah', kameraWajah);
Alpine.data('kioskApp', kioskApp);

if (!window.AlpineInstanceStarted) {
    window.AlpineInstanceStarted = true;
    if (!window.Livewire) {
        Alpine.start();
    }
}

