import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                primary: '#355E3B',
                'primary-light': '#4E8054',
                'primary-dark': '#2A4A30',
                secondary: '#C5A059',
                'secondary-light': '#E5C585',
                'background-light': '#FDFCF8',
                'background-dark': '#0F1714',
                'surface-light': '#FFFFFF',
                'surface-dark': '#1C2622',
            },
            fontFamily: {
                sans: ["'Plus Jakarta Sans'", ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                glass: '0 8px 32px 0 rgba(31, 38, 135, 0.07)',
                'glass-hover': '0 8px 32px 0 rgba(31, 38, 135, 0.15)',
                glow: '0 0 15px rgba(197, 160, 89, 0.3)',
            },
            animation: {
                float: 'float 6s ease-in-out infinite',
            },
            keyframes: {
                float: {
                    '0%, 100%': { transform: 'translateY(0px)' },
                    '50%': { transform: 'translateY(-10px)' },
                },
            },
        },
    },

    plugins: [forms],
};
