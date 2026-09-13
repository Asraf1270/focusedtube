/**
 * FocusedTube — YouTube player tracker.
 *
 * Responsibilities:
 *   1. Fire /started once per page load (view count + history row).
 *   2. Send /progress updates on a throttle (every 10 s while playing).
 *   3. Send /progress on pause, seek, tab hidden, beforeunload.
 *   4. Use navigator.sendBeacon() where available for unload reliability.
 *   5. Never send more than one request per second.
 *
 * Requires: a global `window.FT_PLAYER_CONFIG` object with
 *   { videoId, startedUrl, progressUrl, csrf, resumeAt }
 */

(function () {
    'use strict';

    const cfg = window.FT_PLAYER_CONFIG;
    if (! cfg) return;

    const startedUrl  = cfg.startedUrl;
    const progressUrl = cfg.progressUrl;
    const csrf        = cfg.csrf;

    let player       = null;
    let lastSentAt   = 0;
    let lastPosition = cfg.resumeAt || 0;
    let lastDuration = cfg.duration || 0;
    let startedFired = false;

    const MIN_INTERVAL = 5_000; // ms between progress POSTs while playing.

    function post(url, data, useBeacon) {
        const payload = JSON.stringify(data);
        const headers = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
        };

        if (useBeacon && navigator.sendBeacon) {
            // sendBeacon can't set custom headers, so we must send CSRF
            // in the body. Laravel's VerifyCsrfToken middleware checks
            // the `_token` field for form posts, but for JSON bodies it
            // checks the X-CSRF-TOKEN header only. So sendBeacon won't
            // work with our JSON endpoints as-is.
            //
            // Instead we send a FormData payload with `_token`, which
            // Laravel's CSRF middleware accepts.
            const form = new FormData();
            form.append('_token', csrf);
            Object.keys(data).forEach((k) => form.append(k, data[k]));
            navigator.sendBeacon(url, form);
            return;
        }

        fetch(url, {
            method: 'POST',
            headers,
            body: payload,
            keepalive: true,
        }).catch(() => {
            /* swallow: nothing useful to do if a beacon fails */
        });
    }

    function fireStarted() {
        if (startedFired) return;
        startedFired = true;
        post(startedUrl, {}, false);
    }

    function sendProgress(force) {
        if (! player) return;

        const now = Date.now();
        if (! force && now - lastSentAt < MIN_INTERVAL) return;

        const position = Math.floor(player.getCurrentTime() || 0);
        const duration = Math.floor(player.getDuration() || 0);

        // Ignore noise.
        if (position <= 0 && duration <= 0) return;

        lastSentAt   = now;
        lastPosition = position;
        lastDuration = duration;

        post(progressUrl, { position, duration }, false);
    }

    function sendProgressBeacon() {
        if (! player) return;
        const position = Math.floor(player.getCurrentTime() || 0);
        const duration = Math.floor(player.getDuration() || 0);
        if (position > 0) {
            post(progressUrl, { position, duration }, true);
        }
    }

    // ---- Called by YouTube IFrame API once it's ready ----
    window.onYouTubeIframeAPIReady = function () {
        player = new YT.Player('yt-player', {
            events: {
                onReady: function () {
                    lastDuration = Math.floor(player.getDuration() || 0);

                    // Kick off the tracking loop.
                    fireStarted();

                    // Kick a first progress send after 3 s so we record
                    // "started watching" for the resume feature.
                    setTimeout(() => sendProgress(true), 3000);

                    // Periodic sends while playing.
                    setInterval(() => {
                        const state = player.getPlayerState();
                        if (state === YT.PlayerState.PLAYING) {
                            sendProgress(false);
                        }
                    }, MIN_INTERVAL);
                },
                onStateChange: function (e) {
                    if (e.data === YT.PlayerState.PAUSED) {
                        sendProgress(true);
                    }
                    if (e.data === YT.PlayerState.ENDED) {
                        // Force-send with a nudge so completion logic trips.
                        const duration = Math.floor(player.getDuration() || 0);
                        if (duration > 0) {
                            post(progressUrl, { position: duration, duration }, false);
                        }
                    }
                },
            },
        });
    };

    // ---- Lifecycle triggers ----
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') {
            sendProgressBeacon();
        }
    });

    window.addEventListener('pagehide', sendProgressBeacon);
    window.addEventListener('beforeunload', sendProgressBeacon);
})();