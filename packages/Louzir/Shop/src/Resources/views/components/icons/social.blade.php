<svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
    @switch($icon)
        @case('instagram')
            <g fill="none" stroke="currentColor" stroke-width="1.7">
                <rect x="3" y="3" width="18" height="18" rx="5" />
                <circle cx="12" cy="12" r="4" />
            </g>
            <circle cx="17.5" cy="6.5" r="1" fill="currentColor" />
            @break
        @case('facebook')
            <path fill="currentColor" d="M14 21v-8h2.7l.4-3H14V8.1c0-.9.3-1.5 1.6-1.5H17V3.9a19 19 0 0 0-2.3-.1c-2.3 0-3.9 1.4-3.9 4V10H8v3h2.8v8Z" />
            @break
        @case('tiktok')
            <path fill="currentColor" d="M16.5 3c.4 2.2 1.7 3.6 4 3.9v3.2a9.1 9.1 0 0 1-4-1.2v6.2a6 6 0 1 1-5.2-5.9v3.3a2.8 2.8 0 1 0 2 2.6V3Z" />
            @break
        @case('mail')
            <g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="5" width="18" height="14" rx="3" />
                <path d="m4 7 8 6 8-6" />
            </g>
            @break
    @endswitch
</svg>
