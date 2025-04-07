import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],

    theme: {
        borderRadius: {
            'none': '0',
            'sm': '0.125rem',
            DEFAULT: '0.25rem',
            'md': '8px',
            'lg': '10px',
            'full': '100px',
            'circle': '9999px'
        }
            ,
        extend: {
            fontFamily: {
                body: ['Poppins'], 
                /*sans: ['Figtree', ...defaultTheme.fontFamily.sans]*/
            },
            colors: {
                'primary': '#F5A623',
                'primary-200': '#F0B43A '
            },
            boxShadow: {
                'main': '11px 10px 13px -3px rgba(0,0,0,0.2);'
            }
        },
    },

    plugins: [
        forms,
    ],

    darkMode: 'class',
};
