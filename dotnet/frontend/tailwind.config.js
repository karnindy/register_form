/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#0033A2', // Deep Blue
          light: '#1A54D6',
        },
        secondary: {
          DEFAULT: '#FCAF17', // Gold/Yellow
          hover: '#D98C04',
        },
        background: '#F4F7F6',
        textMain: '#333333',
        textMuted: '#666666',
        border: '#DDDDDD',
        error: '#D32F2F',
        success: '#388E3C',
        info: { DEFAULT: '#0288D1' }, // Bootstrap-like info blue
        warning: { DEFAULT: '#F57C00' }, // Deep orange warning
        danger: { DEFAULT: '#D32F2F' }, // Same as error
      },
      fontFamily: {
        sarabun: ['Sarabun', 'sans-serif'],
      }
    },
  },
  plugins: [],
}
