@inject('collectionContent', 'Webkul\Shop\Helpers\HomeContent')
@php
    $showCategoryDescription = in_array($category->display_mode, [null, 'description_only', 'products_and_description']);
    $collectionChildren = $category->children()->where('status', 1)->orderBy('position')->get();
    $collectionImage = $category->banner_path ? $category->banner_url : ($collectionContent->value('design-category-hero') === '1' ? $collectionContent->value('design-category-image') : null);
@endphp
{!! view_render_event('bagisto.shop.categories.view.banner_path.before') !!}
<div class="louzir-catalog-shell">
    <section @class(['louzir-category-hero' => (bool) $collectionImage]) aria-labelledby="collection-title">
        @if ($collectionImage)
            <x-shop::media.images.lazy class="louzir-category-banner" src="{{ $collectionImage }}" alt="{{ $category->name }}" width="1440" height="480" />
        @endif
        <div class="louzir-collection-heading">
            <h1 id="collection-title" v-pre>{{ $category->name }}</h1>
            {!! view_render_event('bagisto.shop.categories.view.description.before') !!}
            @if ($showCategoryDescription && $category->description)
                <div class="louzir-richtext" v-pre>{!! $category->description !!}</div>
            @endif
            {!! view_render_event('bagisto.shop.categories.view.description.after') !!}
            @if ($collectionImage && in_array($category->display_mode, [null, 'products_only', 'products_and_description']))
                <div class="louzir-collection-actions">
                    <a href="#catalogue" class="primary-button">{{ $collectionContent->value('hero-discover') }}</a>
                    <a href="{{ $collectionContent->value('hero-stores-url') }}" class="secondary-button">{{ $collectionContent->value('hero-stores') }}</a>
                </div>
            @endif
        </div>
    </section>
    @if ($collectionChildren->isNotEmpty())
        <nav class="louzir-category-pills" aria-label="@lang('shop::app.components.layouts.header.desktop.bottom.categories')">
            <a href="#catalogue" aria-current="page" v-pre>{{ $category->name }}</a>
            @foreach ($collectionChildren as $child)
                <a href="{{ $child->url }}" v-pre>{{ $child->name }}</a>
            @endforeach
        </nav>
    @endif
</div>
{!! view_render_event('bagisto.shop.categories.view.banner_path.after') !!}
