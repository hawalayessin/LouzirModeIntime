<?php

namespace Webkul\Shop\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Product\Repositories\ProductRepository;

final class ProductRecommendations
{
    public function __construct(
        private ProductRepository $products,
        private CustomerRepository $customers,
        private RecommendationCache $cache,
    ) {}

    public function related(int $id): Collection
    {
        $settings = RecommendationSettings::current();
        $query = $this->published();
        $source = $this->source($id, $query);

        if ($settings->limit === 0) {
            return collect();
        }

        $context = [core()->getCurrentChannel()->id, app()->getLocale(), $this->customers->getCurrentGroup()->id, $id, $settings->limit, $settings->mode];
        $ids = $this->cache->candidates($context, $settings->cacheSeconds, function () use ($id, $source, $query, $settings) {
            $poolSize = $settings->limit * 4;
            $manual = $settings->mode === 'category' ? [] : (clone $query)
                ->where('pf.product_id', '<>', $id)
                ->whereExists(fn ($q) => $q->selectRaw('1')->from('product_relations')->whereColumn('child_id', 'pf.product_id')->where('parent_id', $id))
                ->orderBy('pf.product_id')->limit($poolSize)->pluck('pf.product_id')->all();

            if ($settings->mode === 'manual' || count($manual) >= $poolSize) {
                return $manual;
            }

            $categories = DB::table('product_categories as source_categories')
                ->join('categories', 'categories.id', '=', 'source_categories.category_id')
                ->where('source_categories.product_id', $id)->where('categories.status', 1)
                ->where('categories.id', '<>', core()->getCurrentChannel()->root_category_id)
                ->pluck('categories.id');
            if ($categories->isEmpty()) {
                return $manual;
            }

            $matches = DB::table('product_categories as pc')->join('categories as c', 'c.id', '=', 'pc.category_id')
                ->whereIn('pc.category_id', $categories)
                ->selectRaw('pc.product_id, MIN(c._rgt - c._lft) as category_span, COUNT(DISTINCT pc.category_id) as shared_categories')
                ->groupBy('pc.product_id');

            $automatic = (clone $query)->joinSub($matches, 'matches', 'matches.product_id', '=', 'pf.product_id')
                ->where('pf.product_id', '<>', $id)->whereNotIn('pf.product_id', $manual)
                ->orderBy('matches.category_span')->orderByDesc('matches.shared_categories')
                ->orderByRaw('ABS(COALESCE(pi.min_price, pf.price, 0) - ?)', [(float) $source->price])
                ->orderBy('pf.product_id')->limit($poolSize - count($manual))->pluck('pf.product_id')->all();

            return array_merge($manual, $automatic);
        });

        return $this->hydrate($ids, $settings->limit, $settings->onlySaleable);
    }

    public function upSells(int $id): Collection
    {
        $this->source($id, $this->published());
        $settings = RecommendationSettings::current();
        if ($settings->upSellLimit === 0) {
            return collect();
        }
        $ids = $this->published()->where('pf.product_id', '<>', $id)
            ->whereExists(fn ($q) => $q->selectRaw('1')->from('product_up_sells')->whereColumn('child_id', 'pf.product_id')->where('parent_id', $id))
            ->orderBy('pf.product_id')->limit($settings->upSellLimit * 4)->pluck('pf.product_id')->all();

        return $this->hydrate($ids, $settings->upSellLimit, $settings->onlySaleable);
    }

    /** One indexed row per product, channel, language and current customer group. */
    private function published(): Builder
    {
        $channel = core()->getCurrentChannel();
        $groupId = $this->customers->getCurrentGroup()->id;

        return DB::table('product_flat as pf')
            ->leftJoin('product_price_indices as pi', fn ($join) => $join->on('pi.product_id', '=', 'pf.product_id')->where('pi.channel_id', $channel->id)->where('pi.customer_group_id', $groupId))
            ->where('pf.channel', $channel->code)->where('pf.locale', app()->getLocale())
            ->where('pf.status', 1)->where('pf.visible_individually', 1)->whereNull('pf.parent_id')
            ->whereNotNull('pf.url_key')->where('pf.url_key', '<>', '')
            ->whereExists(fn ($q) => $q->selectRaw('1')->from('product_channels')->whereColumn('product_id', 'pf.product_id')->where('channel_id', $channel->id));
    }

    private function hydrate(array $ids, int $limit, bool $onlySaleable): Collection
    {
        if (! $ids) {
            return collect();
        }
        // Recheck publication on every hit: a cached ID must never expose a hidden product.
        $eligible = $this->published()->whereIn('pf.product_id', $ids)->pluck('pf.product_id')->all();
        $selected = collect();
        $ordered = array_values(array_intersect($ids, $eligible));
        // Usually one batch is enough; only load further candidates when stock checks remove items.
        foreach (array_chunk($ordered, $limit) as $batch) {
            $products = $this->loadProducts($batch);
            foreach ($batch as $id) {
                $product = $products->get($id);
                if (! $product || ! $product->status || ! $product->visible_individually || ! $product->url_key) {
                    continue;
                }
                if ($onlySaleable && ! $product->getTypeInstance()->isSaleable()) {
                    continue;
                }
                $selected->push($product);
                if ($selected->count() === $limit) {
                    return $selected;
                }
            }
        }

        return $selected;
    }

    private function loadProducts(array $ids): Collection
    {
        return $this->products->getModel()->newQuery()->whereIn('id', $ids)
            ->with(['attribute_family', 'attribute_values', 'images', 'videos', 'price_indices', 'inventory_indices',
                'variants.attribute_family', 'variants.attribute_values', 'variants.price_indices', 'variants.inventory_indices'])
            ->withCount('approvedReviews')->withAvg('approvedReviews', 'rating')->withSum('approvedReviews', 'rating')
            ->get()->keyBy('id');
    }

    private function source(int $id, Builder $query): object
    {
        $source = (clone $query)->where('pf.product_id', $id)->first(['pf.product_id', DB::raw('COALESCE(pi.min_price, pf.price, 0) as price')]);
        abort_unless($source, 404);
        $product = $this->products->getModel()->newQuery()->with(['attribute_family', 'attribute_values'])->find($id);
        abort_unless($product && $product->status && $product->visible_individually && $product->url_key, 404);

        return $source;
    }
}
