/**
 * facefinder.js — Live face detection (v2: face-api.js / tinyFaceDetector).
 * Pengganti MediaPipe WASM — kompatibilitas browser HP jauh lebih baik,
 * tanpa wasm, model ringan (193KB).
 *
 * API (kompatibel dengan versi lama):
 *   window.__faceLoop.start(videoEl, canvasEl)
 *   window.__faceLoop.stop()
 *   window.__faceLoop.detected
 *   window.__faceLoop.resetLiveness()
 *   window.__faceLoop.signalCount — 1 bila wajah terdeteksi (mode tanpa liveness)
 *   window.__faceLoop.livenessAvailable — true
 *
 * Status disiarkan via CustomEvent 'face-state'; tombol #btnCapture di-disable
 * otomatis bila tidak ada wajah. Event 'face-detector-error' bila model gagal.
 */
const VENDOR = '/vendor/faceapi';

let running = false;
let rafId = null;
let videoEl = null;
let canvasEl = null;
let ctx = null;
let lastDetected = false;
let modelPromise = null;

function emit(detected) {
    window.__faceState = { detected, blinkCount: 0, signalCount: detected ? 1 : 0 };
    document.dispatchEvent(new CustomEvent('face-state', {
        detail: { detected, blinkCount: 0, signalCount: detected ? 1 : 0 },
    }));

    const btn = document.getElementById('btnCapture');
    if (btn) {
        btn.disabled = !detected;
        btn.classList.toggle('opacity-50', !detected);
        btn.classList.toggle('cursor-not-allowed', !detected);
    }
    const hint = document.getElementById('faceHint');
    if (hint) {
        hint.textContent = detected ? '✓ WAJAH TERDETEKSI' : 'CARI WAJAH…';
        hint.className = 'text-[11px] font-bold tracking-wider px-3.5 py-1.5 rounded-full backdrop-blur-md transition-all duration-300 ' + (detected ? 'bg-emerald-500/90 text-white shadow-lg shadow-emerald-500/30' : 'bg-red-500/80 text-white shadow-lg shadow-red-500/30');
    }
}

/** Gambar kotak hijau dengan kalkulasi crop object-cover agar sejajar dengan wajah. */
function draw(box) {
    if (!ctx || !canvasEl || !videoEl) return;
    const cw = canvasEl.clientWidth, ch = canvasEl.clientHeight;
    canvasEl.width = cw; canvasEl.height = ch;
    ctx.clearRect(0, 0, cw, ch);
    if (!box) return;

    const vw = videoEl.videoWidth, vh = videoEl.videoHeight;
    if (!vw || !vh) return;
    const s = Math.max(cw / vw, ch / vh);
    const offX = (vw * s - cw) / 2 / s;
    const offY = (vh * s - ch) / 2 / s;

    const x = (box.x - offX) * s;
    const y = (box.y - offY) * s;
    const w = box.w * s;
    const h = box.h * s;

    ctx.strokeStyle = '#22c55e';
    ctx.lineWidth = Math.max(2.5, cw / 320);
    ctx.strokeRect(x, y, w, h);
}

function loadModels() {
    if (!modelPromise) {
        modelPromise = faceapi.nets.tinyFaceDetector.loadFromUri(`${VENDOR}/models`);
    }
    return modelPromise;
}

async function tick() {
    if (!running) return;
    if (videoEl && videoEl.readyState >= 2 && !videoEl.paused && videoEl.videoWidth) {
        try {
            const detections = await faceapi.detectAllFaces(
                videoEl,
                new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.3 })
            );
            const face = detections[0];
            const detected = !!face;
            if (detected) {
                const b = face.box;
                draw({ x: b.x / videoEl.videoWidth, y: b.y / videoEl.videoHeight, w: b.width / videoEl.videoWidth, h: b.height / videoEl.videoHeight });
            } else {
                draw(null);
            }
            if (detected !== lastDetected) {
                lastDetected = detected;
                emit(detected);
            }
        } catch (e) {
            console.error('[facefinder] error frame:', e);
        }
    }
    rafId = requestAnimationFrame(tick);
}

window.__faceLoop = {
    async start(video, canvas) {
        videoEl = video;
        canvasEl = canvas;
        ctx = canvasEl ? canvasEl.getContext('2d') : null;
        running = true;
        lastDetected = false;
        this.resetLiveness();
        emit(false);
        try {
            await loadModels();
            if (!running || !videoEl) return;
            if (rafId) cancelAnimationFrame(rafId);
            rafId = requestAnimationFrame(tick);
        } catch (e) {
            console.error('Gagal memuat model face detection:', e);
            running = false;
            document.dispatchEvent(new CustomEvent('face-detector-error'));
        }
    },

    stop() {
        running = false;
        if (rafId) cancelAnimationFrame(rafId);
        rafId = null;
        if (ctx && canvasEl) {
            canvasEl.width = canvasEl.clientWidth;
            ctx.clearRect(0, 0, canvasEl.width, canvasEl.height);
        }
        lastDetected = false;
        emit(false);
    },

    resetLiveness() {
        // mode tanpa liveness — tidak ada state yang perlu di-reset
    },

    get livenessAvailable() {
        return true;
    },

    get detected() {
        return !!(window.__faceState && window.__faceState.detected);
    },

    get blinkCount() {
        return 0;
    },

    get signalCount() {
        return this.detected ? 1 : 0;
    },

    get signals() {
        return { blink: false, smile: false, depth: false, motion: this.detected };
    },
};
