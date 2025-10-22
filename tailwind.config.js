/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./vendor/robsontenorio/mary/src/**/*.blade.php",
    "./vendor/livewire/**/*.blade.php",
  ],
  theme: {
    extend: {
      colors: {
        dark: {
          50:  '#f9fafb',
          100: '#f3f4f6',
          200: '#e5e7eb',
          300: '#1a1f2d', // 🔧 agora bg-dark-300 funciona
          400: '#121623',
          500: '#0c0f16',
          600: '#0a0c12',
        },
        voch: {
          gold: '#e8c153', // cor amarela VOCH TECH
        },
      },
      fontFamily: {
        inter: ['Inter', 'sans-serif'],
      },
    },
  },
  plugins: [],
};