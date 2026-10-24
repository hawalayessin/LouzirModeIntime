<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Webkul\Category\Contracts\Category;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Core\Contracts\Channel;
use Webkul\Product\Models\Product;

class LingerieCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $unguarded = Model::isUnguarded();
        Model::reguard();

        try {
            $this->createNavigation();
        } finally {
            Model::unguard($unguarded);
        }
    }

    private function createNavigation(): void
    {
        $channel = core()->getDefaultChannel();
        if (! $channel?->root_category_id) {
            throw new RuntimeException('Un canal avec une catégorie racine est nécessaire.');
        }

        $repository = app(CategoryRepository::class);
        $ids = [];
        $created = 0;
        $definitions = require database_path('data/lingerie-categories.php');

        DB::transaction(function () use ($channel, $repository, $definitions, &$ids, &$created) {
            foreach ($definitions as $position => [$slug, $name, $children]) {
                $parent = $this->findOrCreate($repository, $channel, $channel->root_category_id, $slug, $name, $position + 1, $created);
                $ids[$slug] = $parent->id;

                foreach ($children as $childPosition => $childName) {
                    $childSlug = $slug.'-'.Str::slug($childName);
                    $child = $this->findOrCreate($repository, $channel, $parent->id, $childSlug, $childName, $childPosition + 1, $created);
                    $ids[$childSlug] = $child->id;
                }
            }

            // Add useful destinations for known demo items without removing any existing assignment.
            $assignments = [
                'LOUZIR-DEMO-001' => ['pyjamas', 'pyjamas-nuisettes'],
                'LOUZIR-DEMO-002' => ['hauts', 'hauts-bodys'],
                'LOUZIR-DEMO-003' => ['soutiens-gorges', 'soutiens-gorges-ensembles'],
                'LOUZIR-DEMO-004' => ['pyjamas', 'mariage-pyjamas-satin'],
                'LOUZIR-DEMO-005' => ['homme'],
                'LOUZIR-DEMO-006' => ['homme', 'homme-boxers'],
                'LOUZIR-DEMO-008' => ['accessoires'],
            ];
            foreach ($assignments as $sku => $slugs) {
                Product::where('sku', $sku)->first()?->categories()->syncWithoutDetaching(array_map(fn ($slug) => $ids[$slug], $slugs));
            }
        });

        $this->command?->info($created.' catégorie(s) créée(s). Les produits et catégories existants sont conservés.');
    }

    private function findOrCreate(CategoryRepository $repository, Channel $channel, int $parentId, string $slug, string $name, int $position, int &$created): Category
    {
        if ($category = $repository->findBySlug($slug)) {
            if ((int) $category->parent_id !== $parentId) {
                throw new RuntimeException('Le slug '.$slug.' appartient déjà à une autre branche.');
            }

            return $category;
        }

        $data = ['parent_id' => $parentId, 'position' => $position, 'status' => 1, 'display_mode' => 'products_and_description'];
        foreach ($channel->locales as $locale) {
            $data[$locale->code] = ['locale_id' => $locale->id, 'name' => $name, 'slug' => $slug, 'description' => '', 'meta_title' => $name];
        }
        $created++;

        return $repository->create($data);
    }
}
