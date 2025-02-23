/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],
    theme: {
        extend: {
            colors: {
                'ocr-blue': '#4F46E5',
                'ocr-red': '#DC2626',
                'ocr-green': '#059669',
            },
        },
    },
    plugins: [],
};
