import { router } from '@inertiajs/react';
import NProgress from 'nprogress';

NProgress.configure({
  showSpinner: false,
  minimum: 0.1,
});

let timeout = null;

router.on('start', () => {
  timeout = setTimeout(() => NProgress.start(), 250);
});

router.on('progress', (event) => {
  if (NProgress.isStarted() && event.detail.progress.percentage) {
    NProgress.set((event.detail.progress.percentage / 100) * 0.9);
  }
});

router.on('finish', (event) => {
  clearTimeout(timeout);
  if (!NProgress.isStarted()) {
    return;
  } else if (event.detail.visit.completed) {
    NProgress.done();
  } else if (event.detail.visit.interrupted) {
    NProgress.set(0);
  } else if (event.detail.visit.cancelled) {
    NProgress.done();
    NProgress.remove();
  }
});

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

window.axios.pendingRequests = 0;

window.axios.interceptors.request.use(function (config) {
  config.progress === true && NProgress.start();
  window.axios.pendingRequests++;
  return config;
}, function (error) {
  NProgress.done();
  window.axios.pendingRequests--;
  console.error(error)
  return Promise.reject(error);
});

window.axios.interceptors.response.use(function (response) {
  NProgress.done();
  window.axios.pendingRequests--;
  return response;
}, function (error) {
  NProgress.done();
  window.axios.pendingRequests--;
  console.error(error)
  return Promise.reject(error);
});

window.addEventListener("beforeunload", (event) => {
  if (window.axios.pendingRequests > 0) {
    event.preventDefault();
    return (event.returnValue = 'There are pending requests, press "Cancel" to prevent any loss of changes.');
  }
});

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';

import dayjs from 'dayjs';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
  broadcaster: 'pusher',
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
  wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
  wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
  wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
  forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
  enabledTransports: ['ws', 'wss'],
});

// dayjs
import relativeTime from 'dayjs/plugin/relativeTime';
dayjs.extend(relativeTime);
