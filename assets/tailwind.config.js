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
        primary: {
          50: 'var(--primary-color-50)',
          100: 'var(--primary-color-100)',
          200: 'var(--primary-color-200)',
          300: 'var(--primary-color-300)',
          400: 'var(--primary-color-400)',
          500: 'var(--primary-color-500)',
          600: 'var(--primary-color-600)',
          700: 'var(--primary-color-700)',
          800: 'var(--primary-color-800)',
          900: 'var(--primary-color-900)',
          950: 'var(--primary-color-950)',
        },
        secondary: {
          50: 'var(--secondary-color-50)',
          100: 'var(--secondary-color-100)',
          200: 'var(--secondary-color-200)',
          300: 'var(--secondary-color-300)',
          400: 'var(--secondary-color-400)',
          500: 'var(--secondary-color-500)',
          600: 'var(--secondary-color-600)',
          700: 'var(--secondary-color-700)',
          800: 'var(--secondary-color-800)',
          900: 'var(--secondary-color-900)',
          950: 'var(--secondary-color-950)',
        },
      }
    },
  },
  plugins: [
  ],
}
