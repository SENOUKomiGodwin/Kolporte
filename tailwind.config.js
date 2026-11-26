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
            colors: {
                fond: '#FBF7F0',
                encre: '#17231E',
                secondaire: '#55605A',
                vert: '#0F5C45',
                'vert-clair': '#DDEEE6',
                ambre: '#F2B33D',
                'bordure-carte': '#E6DFD3',
                'bordure-champ': '#8C9189',
            },
            fontFamily: {
                display: ['"Bricolage Grotesque"', ...defaultTheme.fontFamily.sans],
                sans: ['"DM Sans"', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
