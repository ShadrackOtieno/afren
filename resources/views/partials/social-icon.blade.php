@php($cls = $cls ?? 'h-4 w-4')
@switch($name)
@case('facebook')<svg class="{{ $cls }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 8h2V5h-2.5C11.2 5 10 6.4 10 8.6V10H8v3h2v6h3v-6h2.2l.3-3H13V8.8c0-.5.3-.8 1-.8z"/></svg>@break
@case('x')<svg class="{{ $cls }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.5 4h2.8l-6.1 7 7.2 9h-5.6l-4.4-5.7L6.4 20H3.6l6.5-7.5L3.2 4h5.7l4 5.2L17.5 4zm-1 14.4h1.5L8.6 5.5H7l9.5 12.9z"/></svg>@break
@case('linkedin')<svg class="{{ $cls }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.5 9H4v11h2.5V9zM5.2 4.5a1.5 1.5 0 100 3 1.5 1.5 0 000-3zM20 20h-2.5v-5.6c0-1.4-.5-2.2-1.7-2.2-1.2 0-1.8.8-1.8 2.2V20H11.5V9H14v1.2c.5-.8 1.4-1.4 2.7-1.4 2.2 0 3.3 1.4 3.3 3.9V20z"/></svg>@break
@case('instagram')<svg class="{{ $cls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="5"/><circle cx="12" cy="12" r="3.6"/><circle cx="17" cy="7" r="1" fill="currentColor" stroke="none"/></svg>@break
@case('youtube')<svg class="{{ $cls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="6" width="18" height="12" rx="4"/><path d="M10 9.5v5l4.5-2.5z" fill="currentColor" stroke="none"/></svg>@break
@case('whatsapp')<svg class="{{ $cls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 4a8 8 0 00-6.9 12L4 20l4.1-1.1A8 8 0 1012 4z"/><path d="M9.5 8.5c0 3 2 5 5 5l1-1.3-1.8-.9-.8.6c-.9-.4-1.6-1.1-2-2l.6-.8-.9-1.8z" fill="currentColor" stroke="none"/></svg>@break
@case('phone')<svg class="{{ $cls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z"/></svg>@break
@case('mail')<svg class="{{ $cls }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>@break
@endswitch
