/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}',
  ],
  theme: {
    borderRadius: {
      'none': '0',
      DEFAULT: '0',
      'sm': '0',
      'md': '0',
      'lg': '0',
      'xl': '0',
      '2xl': '0',
      'full': '0',
    },
    extend: {
      fontFamily: {
        display: ['"Space Mono"', 'monospace'],
        body: ['"DM Sans"', 'sans-serif'],
      },
      colors: {
        bg: 'var(--color-bg)',
        s1: 'var(--color-surface-1)',
        s2: 'var(--color-surface-2)',
        s3: 'var(--color-surface-3)',
        s4: 'var(--color-surface-4)',
        b1: 'var(--color-border-1)',
        b2: 'var(--color-border-2)',
        b3: 'var(--color-border-3)',
        tw: 'var(--color-text)',
        muted: 'var(--color-text-muted)',
        dim: 'var(--color-text-dim)',
        soft: 'var(--color-text-soft)',
        accent: 'var(--color-accent)',
        'accent-dim': 'var(--color-accent-dim)',
        'green-status': 'var(--color-green)',
        'green-dim': 'var(--color-green-dim)',
        'amber-status': 'var(--color-amber)',
        'amber-dim': 'var(--color-amber-dim)',
        'blue-status': 'var(--color-blue)',
        'blue-dim': 'var(--color-blue-dim)',
      },
      keyframes: {
        fadeIn: {
          from: { opacity: '0', transform: 'translateY(6px)' },
          to: { opacity: '1', transform: 'translateY(0)' },
        },
        ticker: {
          from: { transform: 'translateX(0)' },
          to: { transform: 'translateX(-50%)' },
        },
        slideInRight: {
          from: { transform: 'translateX(100%)' },
          to: { transform: 'translateX(0)' },
        },
      },
      animation: {
        'fade-in': 'fadeIn 0.25s ease',
        'ticker': 'ticker 26s linear infinite',
        'ticker-slow': 'ticker 40s linear infinite',
        'slide-in-right': 'slideInRight 0.32s cubic-bezier(0.25,0.46,0.45,0.94)',
      },
    },
  },
  plugins: [],
}
