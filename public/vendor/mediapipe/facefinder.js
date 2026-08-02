/**
 * facefinder.js — Live face detection + LIVENESS multi-sinyal overlay.
 * Self-hosted penuh (tanpa CDN): vision_bundle.mjs + wasm/ + blaze_face_short_range.tflite
 * + face_landmarker.task (blendshape senyum + kedalaman 3D + kedipan).
 *
 * Sinyal liveness (PASIF, cukup 1 lolos):
 *   1) Kedipan mata   — EAR (eye aspect ratio) [PENERIMA]
 *   2) Senyum         — blendshape mouthSmile [PENERIMA]
 *   3) Kedalaman 3D   — rentang koordinat z landmark [INFO]
 *   4) Mikro-gerakan  — selisih piksel area wajah antar frame [INFO]
 *
 * API:
 *   window.__faceLoop.start(videoEl, canvasEl)
 *   window.__faceLoop.stop()
 *   window.__faceLoop.detected
 *   window.__faceLoop.blinkCount
 *   window.__faceLoop.signalCount   — jumlah sinyal penerimaan (kedip+senyum, 0-2)
 *   window.__faceLoop.signals       — rincian {blink, smile, depth, motion}
 *   window.__faceLoop.resetLiveness()
 *
 * Status disiarkan ke UI via CustomEvent 'face-state' dan tombol #btnCapture
 * di-disable otomatis bila tidak ada wajah.
 */
const VENDOR = '/vendor/mediapipe';

let detectorPromise = null;
let landmarkerPromise = null;
let running = false;
let rafId = null;
let videoEl = null;
let canvasEl = null;
let ctx = null;
let lastDetected = false;

// ==== Liveness ====
let blinks = 0;
let eyesClosed = false;
let blinkCooldownUntil = 0;
let smileSeen = false;
let depthSeen = false;
let motionSeen = false;
let prevFaceGray = null;
let livenessAvailable = false;

// Ambang (soft — salah kalibrasi tidak memblokir karena logika ANY-of)
const EAR_CLOSED = 0.20;        // mata dianggap tertutup
const SMILE_MIN = 0.35;         // skor blendshape senyum
const DEPTH_MIN = 0.03;         // rentang z landmark
const MOTION_MIN = 2.0;         // selisih rata-rata piksel (skala 0-255)

// Throttle beban (HP kelas menengah ke bawah)
let frameCounter = 0;
const LANDMARK_EVERY = 3;       // landmarker tiap 3 frame
const MOTION_EVERY = 2;         // motion tiap 2 frame
let lastFaceTime = 0;           // watchdog: kapan terakhir ada wajah

// Indeks landmark mata (FaceMesh 478 titik)
const LEFT_EYE = [33, 160, 158, 133, 153, 144];
const RIGHT_EYE = [362, 385, 387, 263, 373, 380];

function ear(points, idx) {
    const p = (i) => points[idx.indexOf(i)];
    const a = Math.hypot(p(1).x - p(5).x, p(1).y - p(5).y);
    const b = Math.hypot(p(2).x - p(4).x, p(2).y - p(4).y);
    const c = Math.hypot(p(0).x - p(3).x, p(0).y - p(3).y);
    return (a + b) / (2 * c + 1e-6);
}

async function loadDetector() {
    const { FilesetResolver, FaceDetector } = await import(`${VENDOR}/vision_bundle.mjs`);
    const fileset = await FilesetResolver.forVisionTasks(`${VENDOR}/wasm`);
    return FaceDetector.createFromOptions(fileset, {
        baseOptions: { modelAssetPath: `${VENDOR}/blaze_face_short_range.tflite`, delegate: 'CPU' },
        runningMode: 'VIDEO',
        minDetectionConfidence: 0.5,
        numFaces: 1,
    });
}

async function loadLandmarker() {
    const { FilesetResolver, FaceLandmarker } = await import(`${VENDOR}/vision_bundle.mjs`);
    const fileset = await FilesetResolver.forVisionTasks(`${VENDOR}/wasm`);
    const lm = await FaceLandmarker.createFromOptions(fileset, {
        baseOptions: { modelAssetPath: `${VENDOR}/face_landmarker.task`, delegate: 'CPU' },
        runningMode: 'VIDEO',
        numFaces: 1,
        outputFaceBlendshapes: true,
        outputFacialTransformationMatrixes: false,
    });
    livenessAvailable = true;
    return lm;
}

function emit(detected) {
    window.__faceState = { detected, blinkCount: blinks, signalCount: signalCount() };
    document.dispatchEvent(new CustomEvent('face-state', {
        detail: { detected, blinkCount: blinks, signalCount: signalCount() },
    }));

    const btn = document.getElementById('btnCapture');
    if (btn) {
        btn.disabled = !detected;
        if (detected) {
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        }
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

    const cl = ctx.lineWidth * 2;
    ctx.strokeStyle = '#16a34a';
    ctx.lineWidth = cl;
    const cs = Math.min(w, h) * 0.18;
    [
        [x, y, 1, 1], [x + w, y, -1, 1], [x, y + h, 1, -1], [x + w, y + h, -1, -1],
    ].forEach(([cx, cy, dx, dy]) => {
        ctx.beginPath();
        ctx.moveTo(cx + dx * cs, cy);
        ctx.lineTo(cx, cy);
        ctx.lineTo(cx, cy + dy * cs);
        ctx.stroke();
    });
}

/** Sinyal 1: kedipan mata (EAR). */
function processBlink(landmarks) {
    const lm = landmarks?.[0];
    if (!lm || lm.length < 468) return;
    const earL = ear(lm, LEFT_EYE);
    const earR = ear(lm, RIGHT_EYE);
    const avg = (earL + earR) / 2;
    const now = performance.now();
    if (avg < EAR_CLOSED) {
        if (!eyesClosed && now > blinkCooldownUntil) {
            eyesClosed = true;
            blinks++;
            blinkCooldownUntil = now + 700;
            emit(!!window.__faceState?.detected);
        }
    } else {
        eyesClosed = false;
    }
}

/** Sinyal 2: senyum (blendshape). */
function processSmile(blendshapes) {
    if (!blendshapes || smileSeen) return;
    let left = 0, right = 0;
    for (const c of blendshapes) {
        if (c.categoryName === 'mouthSmileLeft') left = c.score;
        if (c.categoryName === 'mouthSmileRight') right = c.score;
    }
    if ((left + right) / 2 >= SMILE_MIN) {
        smileSeen = true;
        emit(!!window.__faceState?.detected);
    }
}

/** Sinyal 3: kedalaman 3D wajah (rentang z landmark). */
function processDepth(landmarks) {
    const lm = landmarks?.[0];
    if (!lm || lm.length < 468 || depthSeen) return;
    let minZ = Infinity, maxZ = -Infinity;
    for (let i = 0; i < 468; i++) {
        const z = lm[i].z;
        if (z < minZ) minZ = z;
        if (z > maxZ) maxZ = z;
    }
    if (maxZ - minZ >= DEPTH_MIN) {
        depthSeen = true;
        emit(!!window.__faceState?.detected);
    }
}

/** Sinyal 4: mikro-gerakan area wajah (selisih piksel antar frame). */
function processMotion(box) {
    if (motionSeen || !videoEl) return;
    const vw = videoEl.videoWidth, vh = videoEl.videoHeight;
    if (!vw || !vh) return;
    const size = 24;
    const c = document.createElement('canvas');
    c.width = size; c.height = size;
    const g = c.getContext('2d', { willReadFrequently: true });
    const sx = Math.max(0, box.x * vw), sy = Math.max(0, box.y * vh);
    const sw = Math.min(vw - sx, box.w * vw), sh = Math.min(vh - sy, box.h * vh);
    if (sw <= 0 || sh <= 0) return;
    g.drawImage(videoEl, sx, sy, sw, sh, 0, 0, size, size);
    const d = g.getImageData(0, 0, size, size).data;
    const gray = new Float32Array(size * size);
    for (let i = 0; i < size * size; i++) {
        gray[i] = 0.299 * d[i * 4] + 0.587 * d[i * 4 + 1] + 0.114 * d[i * 4 + 2];
    }
    if (prevFaceGray) {
        let diff = 0;
        for (let i = 0; i < gray.length; i++) diff += Math.abs(gray[i] - prevFaceGray[i]);
        diff /= gray.length;
        if (diff > MOTION_MIN) {
            motionSeen = true;
            emit(!!window.__faceState?.detected);
        }
    }
    prevFaceGray = gray;
}

/** Jumlah sinyal PENGGUNA UNTUK PENERIMAAN (strong signals): kedip + senyum.
 *  Depth & motion TIDAK dipakai menerima (foto yang dipegang ikut bergerak;
 *  ambang depth perlu kalibrasi lapangan) — hanya info di tracker. */
function signalCount() {
    return [blinks > 0, smileSeen].filter(Boolean).length;
}

async function tick(detector) {
    if (!running) return;
    frameCounter++;
    if (videoEl && videoEl.readyState >= 2 && !videoEl.paused && videoEl.videoWidth) {
        try {
            const res = detector.detectForVideo(videoEl, performance.now());
            const face = res.detections?.[0];
            const detected = !!face;
            if (detected) {
                lastFaceTime = performance.now();
                const b = face.boundingBox;
                const box = { x: b.originX / videoEl.videoWidth, y: b.originY / videoEl.videoHeight, w: b.width / videoEl.videoWidth, h: b.height / videoEl.videoHeight };
                draw(box);

                if (frameCounter % MOTION_EVERY === 0) processMotion(box);

                // Liveness via landmark (best-effort, di-throttle)
                if (landmarker) {
                    if (frameCounter % LANDMARK_EVERY === 0) {
                        try {
                            const lres = landmarker.detectForVideo(videoEl, performance.now());
                            processBlink(lres.faceLandmarks);
                            processSmile(lres.faceBlendshapes?.[0]?.categories);
                            processDepth(lres.faceLandmarks);
                        } catch (e) { /* lewati */ }
                    }
                } else if (!landmarkerPromise) {
                    landmarkerPromise = loadLandmarker()
                        .then(l => { landmarker = l; })
                        .catch(() => {
                            landmarkerPromise = null;
                            livenessAvailable = false;
                            document.dispatchEvent(new CustomEvent('face-liveness-off'));
                        });
                }
            } else {
                draw(null);
                prevFaceGray = null;
            }
            if (detected !== lastDetected) {
                lastDetected = detected;
                emit(detected);
            }

            // Watchdog: deteksi macet walau kamera jalan → log (untuk diagnosa)
            if (detected && performance.now() - lastFaceTime > 3000) {
                console.warn('[facefinder] deteksi lambat/macet, fps rendah');
            }
        } catch (e) {
            console.error('[facefinder] error frame:', e);
        }
    }
    try {
        rafId = requestAnimationFrame(() => tick(detector));
    } catch (e) {
        console.error('[facefinder] rAF gagal:', e);
    }
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
            if (!detectorPromise) detectorPromise = loadDetector();
            const detector = await detectorPromise;
            if (!running || !videoEl) return;
            if (rafId) cancelAnimationFrame(rafId);
            rafId = requestAnimationFrame(() => tick(detector));
        } catch (e) {
            console.error('Gagal memuat face detector:', e);
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
        prevFaceGray = null;
        emit(false);
    },

    resetLiveness() {
        blinks = 0;
        eyesClosed = false;
        smileSeen = false;
        depthSeen = false;
        motionSeen = false;
        prevFaceGray = null;
    },

    get livenessAvailable() {
        return livenessAvailable;
    },

    get detected() {
        return !!(window.__faceState && window.__faceState.detected);
    },

    get blinkCount() {
        return blinks;
    },

    get signalCount() {
        return signalCount();
    },

    get signals() {
        return { blink: blinks > 0, smile: smileSeen, depth: depthSeen, motion: motionSeen };
    },
};
