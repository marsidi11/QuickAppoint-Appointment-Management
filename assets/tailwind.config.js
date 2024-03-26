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
      // Extend the default theme configuration here
    },
  },
  plugins: [
    // Add any plugins here
  ],
}
