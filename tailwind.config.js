import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './config/*.php'
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                'xs': 'var(--size-xs)',
                'sm': 'var(--size-sm)',
                'md': 'var(--size-md)',
                'lg': 'var(--size-lg)',
                'xl': 'var(--size-xl)',
                '2xl': 'var(--size-2xl)',
                '3xl': 'var(--size-3xl)',
                'h1': 'var(--size-h1)',
                'h2': 'var(--size-h2)',
                'h3': 'var(--size-h3)',
                'h4': 'var(--size-h4)',
                'h5': 'var(--size-h5)',
                'h6': 'var(--size-h6)',
            }
        },
    },
    plugins: [
        require('@tailwindcss/typography'),
    ],
};
