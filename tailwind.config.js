import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import colors from 'tailwindcss/colors';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/**/*.php',
    ],

    safelist: [
        'bg-purple-50',
        'bg-purple-100',
        'bg-purple-200',
        'text-purple-700',
        'text-purple-800',
        'text-purple-900',
        'border-purple-200',
        'border-purple-300',
        'bg-purple-500',
        'bg-purple-600',
        'bg-purple-700',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', '"Noto Sans Malayalam"', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#4361ee',
                'primary-hover': '#3a56e4',
                slate: colors.blue,
                indigo: colors.teal,
                purple: colors.purple,
            },
        },
    },

    plugins: [forms],
};
