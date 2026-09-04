import _ from 'lodash';
import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window._ = _;
window.Pusher = Pusher;

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Pull CSRF token from <meta name="csrf-token"> for axios
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
if (reverbKey) {
    // Follow the site domain (restaurant_pos.test, localhost, LAN IP...) so the
    // socket works wherever the app is served. Reverb must listen on 0.0.0.0.
    const pageIsHttps = window.location.protocol === 'https:';
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: window.location.hostname,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: pageIsHttps,
        enabledTransports: pageIsHttps ? ['wss'] : ['ws'],
    });
}
