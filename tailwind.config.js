/** @type {import('tailwindcss').Config} */
import forms from '@tailwindcss/forms';

export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        sinfas: {
          50: '#fdf2f2',
          100: '#fde8e8',
          200: '#fbd0d0',
          300: '#f8a9a9',
          400: '#f27373',
          500: '#e54141',
          600: '#c8232c',
          700: '#b01e25',
          800: '#911c22',
          900: '#791d22',
        }
      }
    },
  },
  plugins: [forms],
}
