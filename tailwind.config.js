import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            colors: {
                brand: {
                    DEFAULT: "#0FA958",
                    light: "#D9F5E8",
                    dark: "#0B7A43",
                },
                neutral: {
                    black: "#1A1A1A",
                    gray: "#374151",
                    soft: "#6B7280",
                    border: "#E5E7EB",
                }
            },
        },
    },
    plugins: [],
};
