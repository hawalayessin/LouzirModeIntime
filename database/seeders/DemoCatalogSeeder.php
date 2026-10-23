<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Helpers\Indexers\Flat;
use Webkul\Product\Helpers\Indexers\Inventory;
use Webkul\Product\Helpers\Indexers\Price;
use Webkul\Product\Repositories\ProductRepository;

class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Artisan seeders disable mass-assignment guards; Bagisto repositories need them.
        $unguarded = Model::isUnguarded();
        Model::reguard();

        try {
            $this->seedCatalog();
        } finally {
            Model::unguard($unguarded);
        }
    }

    private function seedCatalog(): void
    {
        $channel = core()->getDefaultChannel();
        $familyId = DB::table('attribute_families')->where('code', 'default')->value('id');
        $sourceId = $channel?->inventory_sources()->where('status', 1)->value('inventory_sources.id');

        if (! $channel?->root_category_id || ! $familyId || ! $sourceId) {
            throw new RuntimeException('Le canal, la catégorie racine, la famille default et un stock actif sont nécessaires.');
        }

        $categories = [
            'lingerie' => ['Lingerie', 'collection-lingerie'],
            'nuit-homewear' => ['Nuit & Homewear', 'collection-homewear'],
            'homme' => ['Homme', 'collection-family'],
            'pret-a-porter' => ['Prêt-à-porter femme', 'collection-new'],
            'accessoires' => ['Accessoires', 'signature-1'],
        ];

        // Reuse the shop's local visuals so the demo works without external image services.
        $products = [
            ['lingerie', 'Nuisette satin et dentelle marine', 89.900, 'signature-2', 'Nuisette bleu marine à fines finitions en dentelle. Taille M.'],
            ['lingerie', 'Body confort invisible', 59.900, 'signature-3', 'Body doux à finition invisible, coloris nude. Taille M.'],
            ['lingerie', 'Ensemble lingerie dentelle', 79.900, 'collection-lingerie', 'Ensemble de lingerie avec finitions en dentelle. Taille M.'],
            ['nuit-homewear', 'Pyjama satin bleu nuit', 119.900, 'collection-homewear', 'Pyjama deux pièces bleu nuit avec liseré contrastant. Taille M.'],
            ['homme', 'Ensemble détente beige', 129.900, 'signature-4', 'Chemise et pantalon de détente beiges. Taille L.'],
            ['homme', 'Boxer coton confort marine', 24.900, 'collection-family', 'Boxer en coton confortable pour le quotidien. Taille L.'],
            ['pret-a-porter', 'Tailleur femme écru', 189.900, 'collection-new', 'Ensemble veste et pantalon écru. Taille M.'],
            ['accessoires', 'Parure rose poudrée', 99.900, 'signature-1', 'Parure avec collier, boucles d’oreilles, bracelet et bague.'],
        ];

        foreach (array_unique(array_merge(array_column($categories, 1), array_column($products, 3))) as $image) {
            if (! is_file($this->imagePath($image))) {
                throw new RuntimeException('Image de démonstration manquante : '.$image);
            }
        }

        $repository = app(ProductRepository::class);
        $categoryRepository = app(CategoryRepository::class);
        $productIds = [];
        $created = 0;

        DB::transaction(function () use ($channel, $familyId, $sourceId, $categories, $products, $repository, $categoryRepository, &$productIds, &$created) {
            $categoryIds = [];

            foreach ($categories as $key => [$name, $image]) {
                $slug = 'test-'.$key;
                $category = $categoryRepository->findBySlug($slug);

                if (! $category) {
                    $data = [
                        'parent_id' => $channel->root_category_id,
                        'position' => count($categoryIds) + 1,
                        'status' => 1,
                        'display_mode' => 'products_and_description',
                    ];

                    foreach ($channel->locales as $locale) {
                        $data[$locale->code] = [
                            'locale_id' => $locale->id,
                            'name' => $name.' (test)',
                            'slug' => $slug,
                            'description' => '<p>Collection de démonstration Louzir, pour les tests de la boutique.</p>',
                            'meta_title' => $name.' (test)',
                        ];
                    }

                    $category = $categoryRepository->create($data);
                    $category->logo_path = $this->storeImage($image, 'category/'.$category->id.'/demo.webp');
                    $category->banner_path = $category->logo_path;
                    $category->save();
                }

                $categoryIds[$key] = $category->id;
            }

            foreach ($products as $index => [$categoryKey, $name, $price, $image, $description]) {
                $sku = sprintf('LOUZIR-DEMO-%03d', $index + 1);
                $product = $repository->findOneByField('sku', $sku);

                // Preserve any admin edits when the seeder is run again.
                if ($product) {
                    $productIds[] = $product->id;

                    continue;
                }

                $product = $repository->create([
                    'type' => 'simple',
                    'attribute_family_id' => $familyId,
                    'sku' => $sku,
                ]);

                $data = [
                    'channel' => $channel->code,
                    'channels' => [$channel->id],
                    'sku' => $sku,
                    'name' => $name.' (test)',
                    'url_key' => 'test-'.strtolower($sku),
                    'short_description' => '<p>'.$description.'</p>',
                    'description' => '<p>'.$description.'</p><p>Produit de démonstration pour tester la boutique. Visuel illustratif. Taille fixe pour ce produit simple.</p>',
                    'meta_title' => $name.' (test)',
                    'meta_description' => $description,
                    'price' => $price,
                    'weight' => 0.3,
                    'status' => 1,
                    'visible_individually' => 1,
                    'new' => 1,
                    'featured' => 1,
                    'guest_checkout' => 1,
                    'manage_stock' => 1,
                    'categories' => [$categoryIds[$categoryKey]],
                    'inventories' => [$sourceId => 25],
                ];

                foreach ($channel->locales as $locale) {
                    $product = $repository->update([...$data, 'locale' => $locale->code], $product->id);
                }

                $product->images()->create([
                    'type' => 'images',
                    'position' => 1,
                    'path' => $this->storeImage($image, 'product/'.$product->id.'/demo.webp'),
                ]);

                $productIds[] = $product->id;
                $created++;
            }
        });

        // Update only the demo products, synchronously, without requiring a queue worker.
        $demoProducts = $repository->findWhereIn('id', $productIds);
        app(Flat::class)->reindexBatch($demoProducts);
        app(Inventory::class)->reindexBatch($demoProducts);
        app(Price::class)->reindexBatch($demoProducts);

        $this->command?->info('Catalogue de test prêt : 5 catégories, 8 produits avec images. '.$created.' produit(s) créé(s).');
    }

    private function imagePath(string $image): string
    {
        return public_path('assets/home/stitch-2026/'.$image.'.webp');
    }

    private function storeImage(string $image, string $path): string
    {
        if (! Storage::put($path, file_get_contents($this->imagePath($image)))) {
            throw new RuntimeException('Impossible d’enregistrer l’image : '.$path);
        }

        return $path;
    }
}
