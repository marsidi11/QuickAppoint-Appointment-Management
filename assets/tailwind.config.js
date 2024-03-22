/** @type {import('tailwindcss').Config} */

module.exports = {
  content: [
    './js/src/**/*.{vue,js}', // Path to Vue.js files
    './css/src/*.css', // Path to CSS files
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
