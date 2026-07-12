'use strict';

module.exports = {
    name: require('./package').name,

    lazyLoading: {
        enabled: true,
    },

    isDevelopingAddon() {
        return true;
    },
};
