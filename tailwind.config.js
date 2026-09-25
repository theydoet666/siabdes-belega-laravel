/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/Http/Livewire/**/*.php',
        './app/Livewire/**/*.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                'daun': '#2b6446',
                'daun-tua': '#1b4530',
                'kertas': '#f6f7f3',
                'tinta': '#16241d',
                'pena': '#1c3a7a',
                'kuning': '#9a6408',
                'garis': '#d6d9cd',
            },
        },
    },
    plugins: [],
};
