import ENV from '@fleetbase/console/config/environment';

/**
 * Live token streaming for the CBRE Fleet AI panel.
 *
 * The agent bridge publishes response deltas to the `fleet-ai-stream`
 * SocketCluster channel; this initializer renders them progressively inside
 * the AI panel while the request is in flight. Purely additive: the panel's
 * normal request/response flow is untouched, and any socket failure simply
 * means no live preview.
 */
export function initialize() {
    if (typeof window === 'undefined' || !window.socketClusterClient) {
        return;
    }

    let socket;
    try {
        socket = window.socketClusterClient.create({
            hostname: ENV.socket?.hostname || window.location.hostname,
            port: Number(ENV.socket?.port || (window.location.protocol === 'https:' ? 443 : 38000)),
            secure: ENV.socket?.secure !== undefined ? !!ENV.socket.secure : window.location.protocol === 'https:',
            path: ENV.socket?.path || '/socketcluster/',
            autoReconnect: true,
        });
    } catch {
        return;
    }

    let buffer = '';
    let doneTimer = null;

    function panelShell() {
        return document.querySelector('.fleetbase-ai-overlay .fleetbase-ai-shell');
    }

    function liveEl() {
        const shell = panelShell();
        if (!shell) {
            return null;
        }
        let el = shell.querySelector('#cq-ai-live');
        if (!el) {
            el = document.createElement('div');
            el.id = 'cq-ai-live';
            el.innerHTML = '<div class="cq-ai-live-label">CBRE Fleet AI — answering live</div><div class="cq-ai-live-text"></div>';
            shell.insertBefore(el, shell.firstChild);
        }
        return el;
    }

    function render() {
        const el = liveEl();
        if (!el) {
            return;
        }
        const textEl = el.querySelector('.cq-ai-live-text');
        textEl.textContent = buffer;
        el.scrollTop = el.scrollHeight;
    }

    function clear(delayMs) {
        if (doneTimer) {
            clearTimeout(doneTimer);
        }
        doneTimer = setTimeout(() => {
            buffer = '';
            const el = document.getElementById('cq-ai-live');
            if (el) {
                el.remove();
            }
        }, delayMs);
    }

    (async () => {
        try {
            const channel = socket.subscribe('fleet-ai-stream');
            for await (const evt of channel) {
                if (!evt || typeof evt !== 'object') {
                    continue;
                }
                if (evt.kind === 'start') {
                    buffer = '';
                    if (doneTimer) {
                        clearTimeout(doneTimer);
                        doneTimer = null;
                    }
                    render();
                } else if (evt.kind === 'delta' && typeof evt.text === 'string') {
                    buffer += evt.text;
                    render();
                } else if (evt.kind === 'done') {
                    // Panel's own response lands right after; fade the preview.
                    clear(1500);
                }
            }
        } catch {
            // stream is best-effort
        }
    })();
}

export default {
    name: 'ai-live-stream',
    initialize,
};
