import defaultTheme from 'tailwindcss/defaultTheme';
import colors from 'tailwindcss/colors';
import forms from '@tailwindcss/forms';

// Semantic colours are CSS variables (see resources/css/app.css) so every
// utility automatically adapts to light and dark mode.
const token = (name) => `rgb(var(--${name}) / <alpha-value>)`;

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{vue,js}',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                canvas: token('canvas'),
                surface: token('surface'),
                subtle: token('subtle'),
                line: token('line'),
                ink: token('ink'),
                muted: token('muted'),
                brand: colors.emerald,
            },
            boxShadow: {
                card: '0 1px 2px rgb(15 23 42 / 0.04), 0 1px 3px rgb(15 23 42 / 0.05)',
                lift: '0 18px 40px -18px rgb(15 23 42 / 0.35)',
            },
            keyframes: {
                'fade-up': {
                    from: { opacity: '0', transform: 'translateY(8px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
                'pulse-dot': {
                    '0%, 80%, 100%': { transform: 'scale(0.6)', opacity: '0.4' },
                    '40%': { transform: 'scale(1)', opacity: '1' },
                },
            },
            animation: {
                'fade-up': 'fade-up 0.4s ease-out both',
                'pulse-dot': 'pulse-dot 1.2s ease-in-out infinite',
            },
        },
    },

    plugins: [forms],
};
