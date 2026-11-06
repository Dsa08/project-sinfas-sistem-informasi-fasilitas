/**
 * SINFAS — Client-Side Bootstrap & HTTP Client Setup
 * File: resources/js/bootstrap.js
 *
 * Fungsi:
 * - Mengimpor dan mengonfigurasi pustaka HTTP client Axios untuk panggilan AJAX/Fetch ke backend Laravel.
 * - Mengatur header HTTP `X-Requested-With: XMLHttpRequest` agar backend mengenali request asynchronous.
 * - Menyertakan token perlindungan CSRF secara otomatis dari cookie `XSRF-TOKEN`.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
