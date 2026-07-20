/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './assets/**/*.js',
        './templates/**/*.html.twig',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
            },
        },
    },
    plugins: [],
}
