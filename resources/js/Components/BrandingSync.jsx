import { usePage } from '@inertiajs/react';
import { useLayoutEffect } from 'react';

function normalizeHexColor(value, fallback = '#467235') {
    if (typeof value !== 'string') return fallback;
    const trimmed = value.trim();
    return /^#[0-9A-Fa-f]{6}$/.test(trimmed) ? trimmed : fallback;
}

function applyBrandColor(color) {
    const root = document.documentElement;
    const themeColor = normalizeHexColor(color);

    root.style.setProperty('--primary-color', themeColor);
    root.style.setProperty('--accent-color', themeColor);
    root.style.setProperty('--primary-50', 'color-mix(in srgb, var(--primary-color) 10%, white)');
    root.style.setProperty('--primary-100', 'color-mix(in srgb, var(--primary-color) 20%, white)');
    root.style.setProperty('--primary-200', 'color-mix(in srgb, var(--primary-color) 40%, white)');
    root.style.setProperty('--primary-300', 'color-mix(in srgb, var(--primary-color) 60%, white)');
    root.style.setProperty('--primary-400', 'color-mix(in srgb, var(--primary-color) 80%, white)');
    root.style.setProperty('--primary-500', 'var(--primary-color)');
    root.style.setProperty('--primary-600', 'color-mix(in srgb, var(--primary-color) 90%, black)');
    root.style.setProperty('--primary-700', 'color-mix(in srgb, var(--primary-color) 80%, black)');
    root.style.setProperty('--primary-800', 'color-mix(in srgb, var(--primary-color) 60%, black)');
    root.style.setProperty('--primary-900', 'color-mix(in srgb, var(--primary-color) 40%, black)');
    root.style.setProperty('--primary-950', 'color-mix(in srgb, var(--primary-color) 25%, black)');
    root.style.setProperty('--brand-50', 'color-mix(in srgb, var(--primary-color) 10%, white)');
    root.style.setProperty('--brand-100', 'color-mix(in srgb, var(--primary-color) 20%, white)');
    root.style.setProperty('--brand-200', 'color-mix(in srgb, var(--primary-color) 40%, white)');
    root.style.setProperty('--brand-300', 'color-mix(in srgb, var(--primary-color) 60%, white)');
    root.style.setProperty('--brand-400', 'color-mix(in srgb, var(--primary-color) 75%, white)');
    root.style.setProperty('--brand-500', 'color-mix(in srgb, var(--primary-color) 90%, white)');
    root.style.setProperty('--brand-600', 'var(--primary-color)');
    root.style.setProperty('--brand-700', 'color-mix(in srgb, var(--primary-color) 80%, black)');
    root.style.setProperty('--brand-800', 'color-mix(in srgb, var(--primary-color) 65%, black)');
    root.style.setProperty('--brand-900', 'color-mix(in srgb, var(--primary-color) 50%, black)');
    root.style.setProperty('--brand-950', 'color-mix(in srgb, var(--primary-color) 30%, black)');
    root.style.setProperty('--accent-50', 'color-mix(in srgb, var(--accent-color) 10%, white)');
    root.style.setProperty('--accent-100', 'color-mix(in srgb, var(--accent-color) 20%, white)');
    root.style.setProperty('--accent-200', 'color-mix(in srgb, var(--accent-color) 40%, white)');
    root.style.setProperty('--accent-300', 'color-mix(in srgb, var(--accent-color) 60%, white)');
    root.style.setProperty('--accent-400', 'color-mix(in srgb, var(--accent-color) 80%, white)');
    root.style.setProperty('--accent-500', 'var(--accent-color)');
    root.style.setProperty('--accent-600', 'color-mix(in srgb, var(--accent-color) 90%, black)');
    root.style.setProperty('--accent-700', 'color-mix(in srgb, var(--accent-color) 80%, black)');
    root.style.setProperty('--accent-800', 'color-mix(in srgb, var(--accent-color) 60%, black)');
    root.style.setProperty('--accent-900', 'color-mix(in srgb, var(--accent-color) 40%, black)');
    root.style.setProperty('--accent-950', 'color-mix(in srgb, var(--accent-color) 25%, black)');

    let meta = document.head.querySelector('meta[name="theme-color"]');
    if (!meta) {
        meta = document.createElement('meta');
        meta.name = 'theme-color';
        document.head.appendChild(meta);
    }
    meta.setAttribute('content', themeColor);
}

export default function BrandingSync() {
    const brandingColor = usePage().props?.branding?.primary_color;

    useLayoutEffect(() => {
        applyBrandColor(brandingColor);
    }, [brandingColor]);

    return null;
}
