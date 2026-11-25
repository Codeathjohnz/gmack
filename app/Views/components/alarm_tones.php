<?php
/**
 * Gmack — Alarm tone library
 *
 * Five synthesized (Web Audio API) alarm tones, shared between the admin
 * Settings preview button and the cashier POS's live expired/low-stock
 * alarm loop, so what an admin previews is exactly what the cashier hears.
 *
 * Usage: <?= view('components/alarm_tones') ?> then call
 *   playAlarmTone(audioContext, 'classic' | 'siren' | 'pulse' | 'chime' | 'buzzer')
 *
 * Keys here must stay in sync with Admin\Settings::ALARM_SOUNDS.
 */
?>
<script>
// Plays the business's uploaded alarm file. One reusable element, restarted
// on each call, so the alarm loop's repeat interval re-triggers the clip.
let customAlarmAudio = null;
function playAlarmCustom(url) {
    if (!url) return;
    if (!customAlarmAudio || customAlarmAudio.src !== url) {
        customAlarmAudio = new Audio(url);
    }
    customAlarmAudio.currentTime = 0;
    const p = customAlarmAudio.play();
    if (p && p.catch) p.catch(function () { /* blocked until first user interaction */ });
}

// Chooses between the uploaded file and the synthesized tones.
function playAlarmChoice(ctx, kind, customUrl) {
    if (kind === 'custom' && customUrl) {
        playAlarmCustom(customUrl);
        return;
    }
    playAlarmTone(ctx, kind === 'custom' ? 'classic' : kind);
}

function playAlarmTone(ctx, kind) {
    if (!ctx) return;
    switch (kind) {
        case 'siren':  playAlarmSiren(ctx);  break;
        case 'pulse':  playAlarmPulse(ctx);  break;
        case 'chime':  playAlarmChime(ctx);  break;
        case 'buzzer': playAlarmBuzzer(ctx); break;
        case 'classic':
        default:       playAlarmClassic(ctx); break;
    }
}

// Two-step chirp — the original default tone.
function playAlarmClassic(ctx) {
    const now = ctx.currentTime;
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);

    osc.frequency.setValueAtTime(800, now);
    osc.frequency.setValueAtTime(1000, now + 0.1);
    gain.gain.setValueAtTime(0.3, now);
    gain.gain.setValueAtTime(0, now + 0.1);

    osc.start(now);
    osc.stop(now + 0.1);
}

// A rising-and-falling sweep, like a police siren.
function playAlarmSiren(ctx) {
    const now = ctx.currentTime;
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.type = 'sawtooth';
    osc.connect(gain);
    gain.connect(ctx.destination);

    osc.frequency.setValueAtTime(500, now);
    osc.frequency.linearRampToValueAtTime(1100, now + 0.25);
    osc.frequency.linearRampToValueAtTime(500, now + 0.5);
    gain.gain.setValueAtTime(0.22, now);
    gain.gain.setValueAtTime(0, now + 0.5);

    osc.start(now);
    osc.stop(now + 0.5);
}

// Three short, sharp chirps — like a smoke detector.
function playAlarmPulse(ctx) {
    const now = ctx.currentTime;
    for (let i = 0; i < 3; i++) {
        const start = now + i * 0.14;
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'square';
        osc.connect(gain);
        gain.connect(ctx.destination);

        osc.frequency.setValueAtTime(1500, start);
        gain.gain.setValueAtTime(0.22, start);
        gain.gain.setValueAtTime(0, start + 0.06);

        osc.start(start);
        osc.stop(start + 0.06);
    }
}

// A softer descending two-note bell/chime.
function playAlarmChime(ctx) {
    const now = ctx.currentTime;
    const notes = [1046.5, 784]; // C6 -> G5
    notes.forEach(function (freq, i) {
        const start = now + i * 0.18;
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.connect(gain);
        gain.connect(ctx.destination);

        osc.frequency.setValueAtTime(freq, start);
        gain.gain.setValueAtTime(0.28, start);
        gain.gain.exponentialRampToValueAtTime(0.001, start + 0.35);

        osc.start(start);
        osc.stop(start + 0.35);
    });
}

// A low, harsh buzz — like a classic alarm clock.
function playAlarmBuzzer(ctx) {
    const now = ctx.currentTime;
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.type = 'square';
    osc.connect(gain);
    gain.connect(ctx.destination);

    osc.frequency.setValueAtTime(220, now);
    gain.gain.setValueAtTime(0.2, now);
    gain.gain.setValueAtTime(0, now + 0.22);

    osc.start(now);
    osc.stop(now + 0.22);
}
</script>
