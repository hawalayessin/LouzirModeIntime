<!-- SEO Meta Content -->
@push('meta')
    <meta name="title" content="{{ $page->meta_title }}" />

    <meta name="description" content="{{ $page->meta_description }}" />

    <meta name="keywords" content="{{ $page->meta_keywords }}" />
@endPush

@pushOnce('styles')
    <style>
        .louzir-cms-page {
            max-width: 1180px;
            margin-top: 2.5rem;
            margin-bottom: 4rem;
            color: #3f3a36;
            font-family: "Poppins", "Segoe UI", sans-serif;
            line-height: 1.8;
        }

        .louzir-cms-page .static-container {
            padding: clamp(1.5rem, 4vw, 3.5rem);
            border: 1px solid #e6ded7;
            border-radius: 1rem;
            background: #fffdfb;
            box-shadow: 0 14px 40px rgb(64 45 32 / 7%);
        }

        .louzir-cms-page h1,
        .louzir-cms-page h2,
        .louzir-cms-page h3,
        .louzir-cms-page h4 {
            margin: 1.75rem 0 .75rem;
            color: #231f1c;
            font-family: "DM Serif Display", Georgia, serif;
            line-height: 1.2;
        }

        .louzir-cms-page h1 { font-size: clamp(2rem, 4vw, 3rem); }
        .louzir-cms-page h2 { font-size: clamp(1.5rem, 3vw, 2.2rem); }
        .louzir-cms-page h3 { font-size: 1.35rem; }
        .louzir-cms-page p { margin: 0 0 1rem; }
        .louzir-cms-page ul,
        .louzir-cms-page ol { margin: 0 0 1.25rem 1.25rem; padding-left: 1rem; }
        .louzir-cms-page li { margin-bottom: .45rem; }
        .louzir-cms-page a { color: #79573d; text-decoration: underline; text-underline-offset: 3px; }
        .louzir-cms-page blockquote { margin: 1.5rem 0; padding: 1rem 1.25rem; border-left: 3px solid #79573d; background: #f8f2ed; }
        .louzir-cms-page table { display: block; max-width: 100%; overflow-x: auto; border-collapse: collapse; }
        .louzir-cms-page th,
        .louzir-cms-page td { padding: .7rem 1rem; border: 1px solid #e6ded7; text-align: left; }
        .louzir-cms-page th { background: #f8f2ed; color: #231f1c; }

        @media (max-width: 640px) {
            .louzir-cms-page { margin-top: 1.5rem; margin-bottom: 2.5rem; }
            .louzir-cms-page .static-container { padding: 1.25rem; border-radius: .75rem; }
        }
    </style>
@endPushOnce

<!-- Page Layout -->
<x-shop::layouts>
    <!-- Page Title -->
    <x-slot:title>
        {{ $page->meta_title }}
    </x-slot>

    <!-- Page Content -->
    <div class="louzir-cms-page container px-[60px] max-lg:px-8">
        {!! $page->html_content !!}
    </div>
</x-shop::layouts>
