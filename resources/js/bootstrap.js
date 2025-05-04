
//window._ = require('lodash');
import lodash from 'lodash';
import jQuery from 'jquery';
import popper from 'popper.js';
import 'bootstrap';
import introjs from 'intro.js';
import axios from 'axios';
/** Required for select list element */
import 'select2';
/** Import requerido para el uso de Laravel Echo */
import Echo from 'laravel-echo';
import pusher from 'pusher-js';

window._ = lodash;
/** @type {object} Requerido para el uso de JQuery */
window.$ = window.jQuery = jQuery;
/** @type {object} Requerido para el uso de popper.js en Bootstrap 4 */
window.Popper = popper;
/** @type {object} Requerido para tour guiados en funcionalidades del sistema */
window.introJs = introjs;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

/** @type {object} Requerido para el uso de axios */
window.axios = axios;
/** Establece la configuración de la cabecera de las peticiones en axios */
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
/** @type {string} Establece la URL base de las peticiones en axios */
window.axios.defaults.baseURL = window.app_url;

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

/** @type {string} Token CSRF de cada sección en la aplicación */
let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    /** Establece el token csrf para las peticiones en axios */
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    if (window.debug) {
        /** @type {string} Error en consola al no existir un token csrf */
        console.error('No se encuentra el token CSRF, para mayor información consulte: https://laravel.com/docs/csrf#csrf-x-csrf-token');
    } else {
        console.error('Error del formulario. Contacte al administrador');
    }
}

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

/** @type {object} Requerimiento para el uso de pusher en notificaciones */
window.Pusher = pusher;

/** @type {object} Configuración para el uso de Laravel Echo */
window.Echo = new Echo({
    authEndpoint: `${process.env.MIX_APP_URL}/broadcasting/auth`,
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    wsHost: process.env.MIX_WEBSOCKETS_HOST,
    wsPort: process.env.MIX_WEBSOCKETS_PORT,
    wssPort: process.env.MIX_WEBSOCKETS_PORT,
    wsPath: process.env.MIX_WEBSOCKETS_PATH,
    enabledTransports: ['ws', 'wss'],
    encrypted: process.env.MIX_WEBSOCKETS_TLS, //Descomentar al usar protocolos con ssl
    disableStats: true
});
