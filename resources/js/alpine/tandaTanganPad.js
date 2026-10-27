import { cropCanvasKeBoundingBox } from '../lib/gambar';

/**
 * Komponen Alpine.js untuk kanvas tanda tangan halus berbasis kurva kuadratik.
 * Porting murni dari src/komponen/KanvasTandaTangan.jsx
 */
export default function tandaTanganPad() {
    return {
        canvas: null,
        ctx: null,
        isDrawing: false,
        points: [],
        undoStack: [],
        adaCoretan: false,
        warnaTinta: '#1c3a7a', // Token warna pena dinas
        lebarGaris: 3.2,
        resizeObserver: null,

        init() {
            this.canvas = this.$refs.signatureCanvas;
            if (!this.canvas) return;

            this.ctx = this.canvas.getContext('2d', { willReadFrequently: true });
            this.aturUkuranKanvas();

            // Dengarkan event pembersihan kanvas tanda tangan (misal saat ganti peserta atau reset)
            window.addEventListener('bersihkan-tanda-tangan', () => {
                this.bersihkan();
            });

            window.addEventListener('siapkan-kanvas-ttd', () => {
                this.bersihkan();
                this.aturUkuranKanvas(false);
            });

            // ResizeObserver agar kanvas selalu pas saat tab/layar TTD menjadi aktif/terlihat
            if (window.ResizeObserver && this.canvas.parentElement) {
                this.resizeObserver = new ResizeObserver((entries) => {
                    for (const entry of entries) {
                        if (entry.contentRect.width > 0 && entry.contentRect.height > 0) {
                            this.aturUkuranKanvas(this.adaCoretan);
                        }
                    }
                });
                this.resizeObserver.observe(this.canvas.parentElement);
            }

            window.addEventListener('resize', () => {
                this.aturUkuranKanvas(this.adaCoretan);
            });
        },

        aturUkuranKanvas(pertahankanGambar = false) {
            if (!this.canvas || !this.canvas.parentElement) return;

            const rect = this.canvas.parentElement.getBoundingClientRect();
            if (rect.width <= 0 || rect.height <= 0) return;

            const dpr = window.devicePixelRatio || 1;

            let tempCanvas = null;
            if (pertahankanGambar && this.adaCoretan && this.canvas.width > 0 && this.canvas.height > 0) {
                tempCanvas = document.createElement('canvas');
                tempCanvas.width = this.canvas.width;
                tempCanvas.height = this.canvas.height;
                const tempCtx = tempCanvas.getContext('2d');
                if (tempCtx) {
                    tempCtx.drawImage(this.canvas, 0, 0);
                }
            }

            // Set ukuran internal canvas dengan DPR untuk ketajaman retina
            this.canvas.width = Math.floor(rect.width * dpr);
            this.canvas.height = Math.floor(rect.height * dpr);
            this.canvas.style.width = `${rect.width}px`;
            this.canvas.style.height = `${rect.height}px`;

            this.ctx.setTransform(1, 0, 0, 1, 0, 0);
            this.ctx.scale(dpr, dpr);
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';
            this.ctx.strokeStyle = this.warnaTinta;
            this.ctx.lineWidth = this.lebarGaris;

            if (tempCanvas && pertahankanGambar) {
                this.ctx.drawImage(tempCanvas, 0, 0, rect.width, rect.height);
            }
        },

        simpanSnapshot() {
            if (!this.canvas || !this.ctx || this.canvas.width <= 0 || this.canvas.height <= 0) return;
            try {
                const snapshot = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height);
                this.undoStack.push(snapshot);
                if (this.undoStack.length > 20) {
                    this.undoStack.shift();
                }
            } catch (e) {
                console.warn('Gagal menyimpan snapshot kanvas:', e);
            }
        },

        getPointerPos(e) {
            const rect = this.canvas.getBoundingClientRect();
            return {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top,
            };
        },

        mulaiMenggambar(e) {
            if (e.pointerType === 'mouse' && e.button !== 0) return;

            if (this.canvas.width <= 0 || this.canvas.height <= 0) {
                this.aturUkuranKanvas();
            }

            this.simpanSnapshot();
            this.isDrawing = true;
            this.canvas.setPointerCapture(e.pointerId);

            const pos = this.getPointerPos(e);
            this.points = [pos, pos];

            this.ctx.beginPath();
            this.ctx.arc(pos.x, pos.y, this.lebarGaris / 2, 0, Math.PI * 2);
            this.ctx.fillStyle = this.warnaTinta;
            this.ctx.fill();

            this.adaCoretan = true;
        },

        gambar(e) {
            if (!this.isDrawing) return;

            const pos = this.getPointerPos(e);
            this.points.push(pos);

            // Interpolasi kurva kuadratik halus di antara titik
            if (this.points.length >= 3) {
                const p0 = this.points[this.points.length - 3];
                const p1 = this.points[this.points.length - 2];
                const p2 = this.points[this.points.length - 1];

                const mid1 = { x: (p0.x + p1.x) / 2, y: (p0.y + p1.y) / 2 };
                const mid2 = { x: (p1.x + p2.x) / 2, y: (p1.y + p2.y) / 2 };

                this.ctx.beginPath();
                this.ctx.moveTo(mid1.x, mid1.y);
                this.ctx.quadraticCurveTo(p1.x, p1.y, mid2.x, mid2.y);
                this.ctx.stroke();
            }
        },

        selesaiMenggambar(e) {
            if (!this.isDrawing) return;
            this.isDrawing = false;
            try {
                this.canvas.releasePointerCapture(e.pointerId);
            } catch (_) {}

            if (this.points.length === 2) {
                const p1 = this.points[0];
                const p2 = this.points[1];
                this.ctx.beginPath();
                this.ctx.moveTo(p1.x, p1.y);
                this.ctx.lineTo(p2.x, p2.y);
                this.ctx.stroke();
            }

            this.points = [];
        },

        batalkan() {
            if (this.undoStack.length === 0) return;
            const previous = this.undoStack.pop();
            if (this.ctx && this.canvas && this.canvas.width > 0 && this.canvas.height > 0) {
                try {
                    this.ctx.putImageData(previous, 0, 0);
                } catch (e) {
                    console.warn('Gagal membatalkan coretan:', e);
                }
                this.cekApakahAdaCoretan();
            }
        },

        bersihkan(simpanRiwayat = false) {
            if (!this.canvas || !this.ctx) return;
            if (this.canvas.width > 0 && this.canvas.height > 0) {
                if (simpanRiwayat) {
                    this.simpanSnapshot();
                }
                this.ctx.save();
                this.ctx.setTransform(1, 0, 0, 1, 0, 0);
                this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                this.ctx.restore();
            }
            this.adaCoretan = false;
            this.undoStack = [];
            this.points = [];
            this.isDrawing = false;
        },

        cekApakahAdaCoretan() {
            if (!this.canvas || !this.ctx || this.canvas.width <= 0 || this.canvas.height <= 0) {
                this.adaCoretan = false;
                return;
            }
            try {
                const imgData = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height);
                const data = imgData.data;
                for (let i = 3; i < data.length; i += 4) {
                    if (data[i] > 10) {
                        this.adaCoretan = true;
                        return;
                    }
                }
                this.adaCoretan = false;
            } catch (e) {
                console.warn('Gagal memeriksa coretan kanvas:', e);
                this.adaCoretan = false;
            }
        },

        /**
         * Ekspor tanda tangan ke Data URL PNG dengan auto-crop
         */
        eksporPng() {
            if (!this.adaCoretan || !this.canvas || this.canvas.width <= 0 || this.canvas.height <= 0) return null;
            return cropCanvasKeBoundingBox(this.canvas, 16);
        }
    };
}
