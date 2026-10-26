<section class="louzir-product-editorial" aria-label="@lang('shop::app.products.view.description')">
    {!! view_render_event('bagisto.shop.products.view.description.before', ['product' => $product]) !!}
    @if (trim($product->description ?? '') !== '')
        <article class="louzir-product-story" aria-labelledby="product-story-title">
            <div>
                <h2 id="product-story-title">@lang('shop::app.products.view.description')</h2>
            </div>
            <div class="louzir-richtext" data-product-description v-pre>{!! $product->description !!}</div>
        </article>
    @endif
    {!! view_render_event('bagisto.shop.products.view.description.after', ['product' => $product]) !!}

    @if ($attributeData->isNotEmpty())
        <details class="louzir-product-disclosure" data-product-specifications open>
            <summary>@lang('shop::app.products.view.additional-information')</summary>
            <div class="louzir-product-disclosure-content">
                <dl class="louzir-product-specs">
                    @foreach ($attributeData as $attribute)
                        <dt v-pre>{{ $attribute['label'] }}</dt>
                        <dd>
                            @if ($attribute['type'] === 'file')
                                <a href="{{ Storage::url($product[$attribute['code']]) }}" download="{{ $attribute['label'] }}">
                                    <span class="icon-download" aria-hidden="true"></span> {{ $attribute['label'] }}
                                </a>
                            @elseif ($attribute['type'] === 'image')
                                <a href="{{ Storage::url($product[$attribute['code']]) }}">
                                    <img src="{{ Storage::url($attribute['value']) }}" alt="{{ $attribute['label'] }}" width="56" height="56" loading="lazy">
                                </a>
                            @else
                                <span v-pre>{{ $attribute['value'] }}</span>
                            @endif
                        </dd>
                    @endforeach
                </dl>
            </div>
        </details>
    @endif

    <details class="louzir-product-disclosure" id="product-reviews">
        <summary>@lang('shop::app.products.view.review')</summary>
        <div class="louzir-product-disclosure-content">
            @include('shop::products.view.reviews')
        </div>
    </details>
</section>
