'use strict';
const { buildEngine } = require('ember-engines/lib/engine-addon');
const { name } = require('./package');

module.exports = buildEngine({
    name,

    lazyLoading: {
        // Lazy, like all Fleetbase extension engines: the console's extension
        // loader calls loadBundle() for every extension at boot. The host
        // manifest misses non-@fleetbase scopes, so the deploy pipeline
        // injects this engine's bundle into the manifest post-build
        // (see demo-tunnel/patch-manifest.py).
        enabled: true,
    },

    isDevelopingAddon() {
        return true;
    },
});
