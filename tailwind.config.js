import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],

    theme: {
        extend: {
            // ── Design System: Academic Prestige Framework ──────────────────
            // Sourced from stitch/design/material-detail/DESIGN.md
            colors: {
                'surface-bright':             '#f7f9fb',
                'surface':                    '#f7f9fb',
                'background':                 '#f7f9fb',
                'surface-dim':                '#d8dadc',
                'surface-container-lowest':   '#ffffff',
                'surface-container-low':      '#f2f4f6',
                'surface-container':          '#eceef0',
                'surface-container-high':     '#e6e8ea',
                'surface-container-highest':  '#e0e3e5',
                'surface-variant':            '#e0e3e5',
                'surface-tint':               '#3755c3',
                'on-surface':                 '#191c1e',
                'on-surface-variant':         '#434655',
                'on-background':              '#191c1e',
                'primary':                    '#2b4bb9',
                'primary-container':          '#4865d3',
                'primary-fixed':              '#dde1ff',
                'primary-fixed-dim':          '#b8c4ff',
                'on-primary':                 '#ffffff',
                'on-primary-container':       '#efefff',
                'on-primary-fixed':           '#001453',
                'on-primary-fixed-variant':   '#173bab',
                'inverse-primary':            '#b8c4ff',
                'secondary':                  '#006c49',
                'secondary-container':        '#6cf8bb',
                'secondary-fixed':            '#6ffbbe',
                'secondary-fixed-dim':        '#4edea3',
                'on-secondary':               '#ffffff',
                'on-secondary-container':     '#00714d',
                'on-secondary-fixed':         '#002113',
                'on-secondary-fixed-variant': '#005236',
                'tertiary':                   '#784b00',
                'tertiary-container':         '#996100',
                'tertiary-fixed':             '#ffddb8',
                'tertiary-fixed-dim':         '#ffb95f',
                'on-tertiary':                '#ffffff',
                'on-tertiary-container':      '#ffeedd',
                'on-tertiary-fixed':          '#2a1700',
                'on-tertiary-fixed-variant':  '#653e00',
                'outline':                    '#737686',
                'outline-variant':            '#c3c6d7',
                'inverse-surface':            '#2d3133',
                'inverse-on-surface':         '#eff1f3',
                'error':                      '#ba1a1a',
                'error-container':            '#ffdad6',
                'on-error':                   '#ffffff',
                'on-error-container':         '#93000a',
            },

            fontFamily: {
                sans:     ['Public Sans', ...defaultTheme.fontFamily.sans],
                headline: ['Manrope', 'sans-serif'],
                body:     ['Public Sans', 'sans-serif'],
                label:    ['Public Sans', 'sans-serif'],
            },

            borderRadius: {
                DEFAULT: '0.25rem',
                lg:      '0.5rem',
                xl:      '0.75rem',
                '2xl':   '1.5rem',
                full:    '9999px',
            },
        },
    },

    plugins: [forms],
};
