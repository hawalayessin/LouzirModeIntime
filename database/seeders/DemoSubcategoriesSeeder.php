<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Repositories\ProductRepository;

class DemoSubcategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $unguarded = Model::isUnguarded();
        Model::reguard();

        try {
            $this->seedCategories();
        } finally {
            Model::unguard($unguarded);
        }
    }

    private function seedCategories(): void
    {
        $definitions = [
            ['nuisettes', 'lingerie', ['fr' => 'Nuisettes', 'en' => 'Nightdresses', 'ar' => 'قمصان النوم'], 'signature-2', 'LOUZIR-DEMO-001'],
            ['bodies', 'lingerie', ['fr' => 'Bodies', 'en' => 'Bodysuits', 'ar' => 'بودي'], 'signature-3', 'LOUZIR-DEMO-002'],
            ['ensembles-lingerie', 'lingerie', ['fr' => 'Ensembles de lingerie', 'en' => 'Lingerie sets', 'ar' => 'أطقم ملابس داخلية'], 'collection-lingerie', 'LOUZIR-DEMO-003'],
            ['pyjamas', 'nuit-homewear', ['fr' => 'Pyjamas', 'en' => 'Pyjamas', 'ar' => 'بيجامات'], 'collection-homewear', 'LOUZIR-DEMO-004'],
            ['tenues-detente', 'homme', ['fr' => 'Tenues de détente', 'en' => 'Loungewear', 'ar' => 'ملابس الراحة'], 'signature-4', 'LOUZIR-DEMO-005'],
            ['bijoux', 'accessoires', ['fr' => 'Bijoux', 'en' => 'Jewellery', 'ar' => 'مجوهرات'], 'signature-1', 'LOUZIR-DEMO-008'],
        ];
        $categories = app(CategoryRepository::class);
        $products = app(ProductRepository::class);
        $channel = core()->getDefaultChannel();
        $created = 0;

        // Check all dependencies before creating categories or writing their images.
        foreach ($definitions as [$slug, $parentSlug, $names, $image, $sku]) {
            if (! $categories->findBySlug('test-'.$parentSlug) || ! $products->findOneByField('sku', $sku)) {
                throw new RuntimeException('Le catalogue de démonstration doit exister avant les sous-catégories.');
            }
            if (! is_file(public_path('assets/home/stitch-2026/'.$image.'.webp'))) {
                throw new RuntimeException('Image de catégorie manquante : '.$image);
            }
        }

        DB::transaction(function () use ($definitions, $categories, $products, $channel, &$created) {
            foreach ($definitions as $position => [$slug, $parentSlug, $names, $image, $sku]) {
                $parent = $categories->findBySlug('test-'.$parentSlug);
                $category = $categories->findBySlug('test-'.$slug);

                if (! $category) {
                    $data = [
                        'parent_id' => $parent->id,
                        'position' => $position + 1,
                        'status' => 1,
                        'display_mode' => 'products_and_description',
                    ];

                    foreach ($channel->locales as $locale) {
                        $name = ($names[$locale->code] ?? $names['fr']).' (test)';
                        $data[$locale->code] = [
                            'locale_id' => $locale->id,
                            'name' => $name,
                            'slug' => 'test-'.$slug,
                            'description' => '<p>'.$name.' — Louzir Mode Intime.</p>',
                            'meta_title' => $name,
                        ];
                    }

                    $category = $categories->create($data);
                    if ($category->translations()->count() !== $channel->locales->count()) {
                        throw new RuntimeException('Les langues des catégories doivent être rechargées avant de continuer.');
                    }
                    $path = 'category/'.$category->id.'/demo.webp';
                    if (! Storage::put($path, file_get_contents(public_path('assets/home/stitch-2026/'.$image.'.webp')))) {
                        throw new RuntimeException('Impossible d’enregistrer l’image : '.$path);
                    }
                    $category->logo_path = $path;
                    $category->banner_path = $path;
                    $category->save();
                    $created++;
                }

                // Keep the existing parent category and any merchant assignments.
                $products->findOneByField('sku', $sku)->categories()->syncWithoutDetaching([$category->id]);
            }
        });

        $this->command?->info('6 sous-catégories de test prêtes avec images et produits. '.$created.' catégorie(s) créée(s).');
    }
}
