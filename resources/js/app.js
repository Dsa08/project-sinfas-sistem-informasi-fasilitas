/**
 * SINFAS — Client-Side Scripts (Frontend Entry Point)
 * File: resources/js/app.js
 * 
 * Fungsi:
 * - Mengimpor konfigurasi dasar axios & header HTTP XMLHttpRequest dari bootstrap.js.
 * - Mengimpor pustaka visualisasi Chart.js (auto mode) dan mengeksposnya ke objek global `window.Chart`.
 * - Memungkinkan inisialisasi diagram analitik pada dashboard admin sarana secara dinamis.
 */

import './bootstrap';

import Chart from 'chart.js/auto';
window.Chart = Chart;
