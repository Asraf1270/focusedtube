/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./app/**/*.php",
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          50:  '#eef4ff',
          100: '#dbe6ff',
          200: '#bcd0ff',
          300: '#8eb0ff',
          400: '#5a86ff',
          500: '#3563ff',
          600: '#2148e6',
          700: '#1a39b4',
          800: '#1a3390',
          900: '#1b2f72',
        },
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [require('@tailwindcss/forms')],
};