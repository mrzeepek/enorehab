// tailwind.config.js - à mettre à jour
module.exports = {
    content: [
      './templates/**/*.twig',
      './assets/js/**/*.js',
    ],
    theme: {
      extend: {
        colors: {
          'primary': '#000000',  // Fond global
          'secondary': '#111111',  // Sections alternées
          'accent': {
            DEFAULT: '#0ed0ff',  // Accent & icônes
            'dark': '#00b5e2',   // Hover
          },
        },
        fontFamily: {
          'sans': ['Inter', 'sans-serif'],
          'display': ['Montserrat', 'sans-serif'],
        },
        animation: {
          'fade-in': 'fadeIn 1.2s ease-out forwards',
          'pulse': 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
        },
        keyframes: {
          fadeIn: {
            '0%': { opacity: '0', transform: 'translateY(20px)' },
            '100%': { opacity: '1', transform: 'translateY(0)' },
          },
          pulse: {
            '0%, 100%': { opacity: '1' },
            '50%': { opacity: '.5' },
          },
        },
      }
    },
    plugins: [
      require('@tailwindcss/forms'),
    ],
  }

  