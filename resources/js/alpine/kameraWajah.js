import { kompresiFotoVideo } from '../lib/gambar';

/**
 * Komponen Alpine.js untuk pengambilan foto wajah dengan timer 3 detik, beep, dan flash efek.
 * Porting murni dari src/komponen/AmbilFoto.jsx
 */
export default function kameraWajah() {
    return {
        stream: null,
        videoEl: null,
        kameraAktif: false,
        kameraTersedia: true,
        pesanErrorKamera: null,
        isCountingDown: false,
        countdown: 3,
        countdownInterval: null,
        flashActive: false,
        fotoBase64: null,

        init() {
            this.videoEl = this.$refs.videoElement;

            window.addEventListener('siapkan-kamera-wajah', () => {
                this.resetDanMulai();
            });

            window.addEventListener('hentikan-kamera-wajah', () => {
                this.hentikanKamera();
            });

            // Mulai kamera jika saat init layar sudah berada di foto
            if (this.layar === 'foto') {
                this.resetDanMulai();
            }
        },

        resetDanMulai() {
            this.fotoBase64 = null;
            this.isCountingDown = false;
            this.countdown = 3;
            if (this.countdownInterval) {
                clearInterval(this.countdownInterval);
                this.countdownInterval = null;
            }
            this.mulaiKamera();
        },

        async mulaiKamera() {
            this.pesanErrorKamera = null;
            this.fotoBase64 = null;

            // Hentikan stream sebelumnya jika masih tertinggal agar resource kamera segar
            if (this.stream) {
                this.stream.getTracks().forEach(track => {
                    try {
                        track.stop();
                    } catch (e) {
                        console.warn('Error stopping track:', e);
                    }
                });
                this.stream = null;
            }

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.kameraTersedia = false;
                this.pesanErrorKamera = 'Perangkat ini tidak mendukung akses kamera web.';
                return;
            }

            try {
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                    },
                    audio: false,
                });

                if (!this.videoEl) {
                    this.videoEl = this.$refs.videoElement;
                }

                if (this.videoEl) {
                    this.videoEl.srcObject = this.stream;
                    try {
                        await this.videoEl.play();
                    } catch (playErr) {
                        console.warn('[SIABDES Kamera] Gagal play video otomatis:', playErr);
                    }
                }

                this.kameraAktif = true;
                this.kameraTersedia = true;
            } catch (err) {
                console.warn('[SIABDES Kamera] Gagal mengakses kamera:', err);
                this.kameraAktif = false;
                this.pesanErrorKamera = 'Izin kamera ditolak atau kamera sedang digunakan aplikasi lain.';
            }
        },

        hentikanKamera() {
            if (this.stream) {
                this.stream.getTracks().forEach(track => {
                    try {
                        track.stop();
                    } catch (e) {
                        console.warn('Error stopping track:', e);
                    }
                });
                this.stream = null;
            }
            if (this.videoEl) {
                this.videoEl.srcObject = null;
            }
            this.kameraAktif = false;
            this.isCountingDown = false;
            if (this.countdownInterval) {
                clearInterval(this.countdownInterval);
                this.countdownInterval = null;
            }
        },

        playBeep(frekuensi = 880, durasi = 0.1) {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const audioCtx = new AudioCtx();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();

                osc.type = 'sine';
                osc.frequency.setValueAtTime(frekuensi, audioCtx.currentTime);

                gain.gain.setValueAtTime(0.25, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + durasi);

                osc.connect(gain);
                gain.connect(audioCtx.destination);

                osc.start();
                osc.stop(audioCtx.currentTime + durasi);
            } catch (e) {
                console.warn('Web Audio API beep error:', e);
            }
        },

        mulaiCountdown() {
            if (this.isCountingDown || !this.kameraAktif) return;

            this.isCountingDown = true;
            this.countdown = 3;
            this.playBeep(880, 0.12);

            this.countdownInterval = setInterval(() => {
                this.countdown--;

                if (this.countdown > 0) {
                    this.playBeep(880, 0.12);
                } else if (this.countdown === 0) {
                    clearInterval(this.countdownInterval);
                    this.countdownInterval = null;

                    // Beep nada tinggi saat jepret
                    this.playBeep(1760, 0.25);

                    // Efek Flash Putih
                    this.flashActive = true;
                    setTimeout(() => {
                        this.flashActive = false;
                    }, 250);

                    // Ambil foto dari video
                    this.ambilFotoSekarang();
                }
            }, 1000);
        },

        ambilFotoSekarang() {
            if (!this.videoEl) return;
            this.fotoBase64 = kompresiFotoVideo(this.videoEl, 800, 0.82);
            this.isCountingDown = false;
            this.hentikanKamera();
        },

        fotoUlang() {
            this.fotoBase64 = null;
            this.isCountingDown = false;
            this.mulaiKamera();
        }
    };
}
