/** @type {import('tailwindcss').Config} */

module.exports = {
  content: [
    './admin/js/**/*.{vue,js}', // Path to Vue.js files
    './frontend/js/**/*.{vue,js}', // Path to Vue.js files
    './admin/css/**/*.css', // Path to CSS files
    './frontend/css/**/*.css', // Path to CSS files
    '../templates/*.php', // Path to PHP files in the template folder
  ],
  theme: {
    extend: {
      colors: {
        primary: { "50": "#eff6ff", "100": "#dbeafe", "200": "#bfdbfe", "300": "#93c5fd", "400": "#60a5fa", "500": "#3b82f6", "600": "#2563eb", "700": "#1d4ed8", "800": "#1e40af", "900": "#1e3a8a", "950": "#172554" }
      }
    },
  },
  plugins: [
    // Add any plugins here
  ],
}
