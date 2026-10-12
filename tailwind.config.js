import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
                heading: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                bsds: {
                    navy: '#17324D',
                    teal: '#1F8A8A',
                    orange: '#F4A261',
                    bg: '#F7F9FC',
                    ink: '#17212B',
                    gray: '#64748B',
                    border: '#E2E8F0',
                    success: '#3A9D6E',
                    warning: '#E9A23B',
                    error: '#D95C5C',
                },
            },
        },
    },

    plugins: [forms],
};
