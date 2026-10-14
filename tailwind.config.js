import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

// Tokens del design system de Stitch "Industrial Inventory & Quotation" (FerroTek)
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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#142c3a',
                'primary-container': '#2b4251',
                'primary-hover': '#435d6c',
                'on-primary': '#ffffff',
                'on-primary-container': '#96aec0',
                'primary-fixed': '#cde6f9',
                secondary: '#486271',
                'secondary-container': '#c8e4f6',
                tertiary: '#1e2b34',
                background: '#faf9f6',
                surface: '#faf9f6',
                'surface-container-lowest': '#ffffff',
                'surface-container-low': '#f4f3f0',
                'surface-container': '#efeeeb',
                'surface-container-high': '#e9e8e5',
                'surface-container-highest': '#e3e2df',
                'on-surface': '#1b1c1a',
                'on-surface-variant': '#43474b',
                muted: '#5a6065',
                outline: '#73787c',
                'outline-variant': '#c3c7cc',
                line: '#d8d6cf',
                'line-soft': '#eae8e1',
                ok: '#2d5a3c',
                warn: '#8a611c',
                danger: '#8b2e2b',
                error: '#ba1a1a',
            },
            borderRadius: {
                DEFAULT: '2px',
                sm: '2px',
                md: '2px',
                lg: '4px',
            },
            fontSize: {
                'display-lg': ['2rem', { lineHeight: '2.5rem', letterSpacing: '-0.02em', fontWeight: '600' }],
                'headline-lg': ['1.5rem', { lineHeight: '2rem', letterSpacing: '-0.015em', fontWeight: '600' }],
                'headline-md': ['1.25rem', { lineHeight: '1.75rem', letterSpacing: '-0.01em', fontWeight: '600' }],
                'headline-sm': ['1.125rem', { lineHeight: '1.5rem', letterSpacing: '-0.005em', fontWeight: '600' }],
                'body-lg': ['1rem', { lineHeight: '1.5rem' }],
                'body-md': ['0.875rem', { lineHeight: '1.25rem' }],
                'body-sm': ['0.75rem', { lineHeight: '1rem' }],
                'label-lg': ['0.875rem', { lineHeight: '1.25rem', letterSpacing: '0.01em', fontWeight: '500' }],
                'label-md': ['0.75rem', { lineHeight: '1rem', letterSpacing: '0.02em', fontWeight: '600' }],
                'label-sm': ['0.6875rem', { lineHeight: '0.875rem', letterSpacing: '0.04em', fontWeight: '600' }],
                'code-num': ['0.8125rem', { lineHeight: '1rem', letterSpacing: '0.02em', fontWeight: '500' }],
            },
        },
    },

    plugins: [forms],
};
