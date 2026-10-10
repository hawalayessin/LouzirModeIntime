<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Webkul\Category\Repositories\CategoryRepository;

class StorefrontLocalizationSeeder extends Seeder
{
    /**
     * Add storefront categories, CMS translations, and theme-copy translations.
     */
    public function run(): void
    {
        $this->seedCategories();

        $this->seedPages();

        $this->seedThemeCustomizations();
    }

    protected function seedCategories(): void
    {
        $categories = [
            'root' => ['en' => 'Root', 'fr' => 'Racine', 'ar' => 'الرئيسية'],
            'electronics' => ['en' => 'Electronics', 'fr' => 'Électronique', 'ar' => 'الإلكترونيات'],
            'mens' => ['en' => 'Men', 'fr' => 'Hommes', 'ar' => 'الرجال'],
            'womens' => ['en' => 'Women', 'fr' => 'Femmes', 'ar' => 'النساء'],
            'formal-wear-men' => ['en' => 'Formal Wear Men', 'fr' => 'Tenues habillées homme', 'ar' => 'ملابس الرجال الرسمية'],
            'formal-wear-female' => ['en' => 'Formal Wear Women', 'fr' => 'Tenues habillées femme', 'ar' => 'ملابس النساء الرسمية'],
            'wellness' => ['en' => 'Wellness', 'fr' => 'Bien-être', 'ar' => 'العناية والرفاه'],
            'active-wear' => ['en' => 'Active Wear', 'fr' => 'Vêtements de sport', 'ar' => 'ملابس رياضية'],
            'active-wear-female' => ['en' => 'Active Wear Women', 'fr' => 'Vêtements de sport femme', 'ar' => 'ملابس رياضية نسائية'],
            'boxer' => ['en' => 'Boxer', 'fr' => 'Boxer', 'ar' => 'بوكسر'],
        ];

        $locales = DB::table('locales')->whereIn('code', ['en', 'fr', 'ar'])->get()->keyBy('code');
        $repository = app(CategoryRepository::class);

        foreach ($categories as $slug => $names) {
            $categoryId = DB::table('category_translations')
                ->where('slug', $slug)
                ->value('category_id');

            if (! $categoryId) {
                $translationData = [];

                foreach ($names as $locale => $name) {
                    $translationData[$locale] = [
                        'name' => $name,
                        'slug' => $slug,
                        'description' => '',
                    ];
                }

                $category = $repository->create([
                    ...$translationData,
                    'parent_id' => 1,
                    'position' => 1,
                    'status' => 1,
                    'display_mode' => 'products_and_description',
                ]);

                $categoryId = $category->id;
            }

            foreach ($names as $locale => $name) {
                if (! isset($locales[$locale])) {
                    continue;
                }

                $translationValues = [
                    'name' => $name,
                    'url_path' => $slug,
                    'locale_id' => $locales[$locale]->id,
                ];

                if ($slug === 'boxer') {
                    $translationValues['description'] = [
                        'en' => '<p>Boxer Coton</p>',
                        'fr' => '<p>Boxer en coton</p>',
                        'ar' => '<p>بوكسر قطني</p>',
                    ][$locale];
                }

                DB::table('category_translations')->updateOrInsert([
                    'category_id' => $categoryId,
                    'slug' => $slug,
                    'locale' => $locale,
                ], $translationValues);
            }
        }
    }

    protected function seedPages(): void
    {
        $pages = [
            'about-us' => [
                'fr' => ['title' => 'À propos', 'content' => 'Contenu de la page À propos.'],
                'ar' => ['title' => 'من نحن', 'content' => 'محتوى صفحة من نحن.'],
            ],
            'customer-service' => [
                'fr' => ['title' => 'Service client', 'content' => 'Contenu de la page Service client.'],
                'ar' => ['title' => 'خدمة العملاء', 'content' => 'محتوى صفحة خدمة العملاء.'],
            ],
            'payment-policy' => [
                'fr' => ['title' => 'Politique de paiement', 'content' => 'Contenu de la page Politique de paiement.'],
                'ar' => ['title' => 'سياسة الدفع', 'content' => 'محتوى صفحة سياسة الدفع.'],
            ],
            'privacy-policy' => [
                'fr' => ['title' => 'Politique de confidentialité', 'content' => 'Contenu de la page Politique de confidentialité.'],
                'ar' => ['title' => 'سياسة الخصوصية', 'content' => 'محتوى صفحة سياسة الخصوصية.'],
            ],
            'refund-policy' => [
                'fr' => ['title' => 'Politique de remboursement', 'content' => 'Contenu de la page Politique de remboursement.'],
                'ar' => ['title' => 'سياسة استرداد الأموال', 'content' => 'محتوى صفحة سياسة استرداد الأموال.'],
            ],
            'return-policy' => [
                'fr' => ['title' => 'Politique de retour', 'content' => 'Contenu de la page Politique de retour.'],
                'ar' => ['title' => 'سياسة الإرجاع', 'content' => 'محتوى صفحة سياسة الإرجاع.'],
            ],
            'shipping-policy' => [
                'fr' => ['title' => 'Politique de livraison', 'content' => 'Contenu de la page Politique de livraison.'],
                'ar' => ['title' => 'سياسة الشحن', 'content' => 'محتوى صفحة سياسة الشحن.'],
            ],
            'terms-conditions' => [
                'fr' => ['title' => 'Conditions générales', 'content' => 'Contenu de la page Conditions générales.'],
                'ar' => ['title' => 'الشروط والأحكام', 'content' => 'محتوى صفحة الشروط والأحكام.'],
            ],
            'terms-of-use' => [
                'fr' => ['title' => 'Conditions d’utilisation', 'content' => 'Contenu de la page Conditions d’utilisation.'],
                'ar' => ['title' => 'شروط الاستخدام', 'content' => 'محتوى صفحة شروط الاستخدام.'],
            ],
            'whats-new' => [
                'fr' => ['title' => 'Nouveautés', 'content' => 'Contenu de la page Nouveautés.'],
                'ar' => ['title' => 'ما الجديد', 'content' => 'محتوى صفحة ما الجديد.'],
            ],
        ];

        foreach ($pages as $urlKey => $translations) {
            $source = DB::table('cms_page_translations')
                ->where('url_key', $urlKey)
                ->where('locale', 'en')
                ->first();

            if (! $source) {
                continue;
            }

            foreach ($translations as $locale => $translation) {
                DB::table('cms_page_translations')->updateOrInsert([
                    'cms_page_id' => $source->cms_page_id,
                    'locale' => $locale,
                ], [
                    'url_key' => $urlKey,
                    'page_title' => $translation['title'],
                    'html_content' => '<div class="static-container"><div class="mb-5">'.$translation['content'].'</div></div>',
                    'meta_title' => $translation['title'],
                    'meta_description' => $translation['content'],
                    'meta_keywords' => str_replace('-', ' ', $urlKey),
                ]);
            }
        }
    }

    protected function seedThemeCustomizations(): void
    {
        $translations = [
            'fr' => [
                'Get Ready For New Collection' => 'Découvrez la nouvelle collection',
                'Get UPTO 40% OFF on your 1st order SHOP NOW' => 'Jusqu’à 40 % de réduction sur votre première commande. Profitez-en !',
                'Introducing Our New Bold Collections! Elevate your style with daring designs and vibrant statements. Explore striking patterns and bold colors that redefine your wardrobe. Get ready to embrace the extraordinary!' => 'Découvrez nos nouvelles collections audacieuses ! Affirmez votre style avec des modèles et des couleurs vibrants. Explorez des motifs remarquables et des teintes intenses qui réinventent votre garde-robe.',
                'Our Bold Collections are here to redefine your wardrobe with fearless designs and striking, vibrant colors. From daring patterns to powerful hues, this is your chance to break away from the ordinary and step into the extraordinary.' => 'Nos collections audacieuses réinventent votre garde-robe avec des modèles affirmés et des couleurs éclatantes. Osez les motifs et les teintes intenses pour sortir de l’ordinaire.',
                'Unleash Your Boldness with Our New Collection!' => 'Affirmez votre style avec notre nouvelle collection !',
                'Get Ready for our new Bold Collections!' => 'Découvrez nos nouvelles collections audacieuses !',
                'Women\'s Collections' => 'Collections femme',
                'Men\'s Collections' => 'Collections homme',
                'Kid\'s Collection' => 'Collection enfant',
                'The game with our new additions!' => 'Découvrez nos nouveautés !',
                'Dedicated 24/7 support via chat and email' => 'Une assistance dédiée par chat et e-mail, 24 h/24 et 7 j/7',
                'No cost EMI available on all major credit cards' => 'Paiement sans frais disponible avec les principales cartes bancaires',
                'Easy Product Replacement Available!' => 'Échange de produit facile !',
                'Enjoy free shipping on all orders' => 'Profitez de la livraison gratuite sur toutes les commandes',
                'Terms & Conditions' => 'Conditions générales',
                'Terms of Use' => 'Conditions d’utilisation',
                'Privacy Policy' => 'Politique de confidentialité',
                'Customer Service' => 'Service client',
                'Payment Policy' => 'Politique de paiement',
                'Shipping Policy' => 'Politique de livraison',
                'Refund Policy' => 'Politique de remboursement',
                'Return Policy' => 'Politique de retour',
                'What\'s New' => 'Nouveautés',
                'View Collections' => 'Voir les collections',
                'Our Collections' => 'Nos collections',
                'Free Shipping' => 'Livraison gratuite',
                'Product Replace' => 'Remplacement de produit',
                'Emi Available' => 'Paiement en plusieurs fois',
                '24/7 Support' => 'Assistance 24 h/24 et 7 j/7',
                'Book Tickets' => 'Réserver',
                'About Us' => 'À propos',
                'Contact Us' => 'Contactez-nous',
            ],
            'ar' => [
                'Get Ready For New Collection' => 'اكتشفوا المجموعة الجديدة',
                'Get UPTO 40% OFF on your 1st order SHOP NOW' => 'خصم يصل إلى 40٪ على طلبكم الأول. تسوقوا الآن!',
                'Introducing Our New Bold Collections! Elevate your style with daring designs and vibrant statements. Explore striking patterns and bold colors that redefine your wardrobe. Get ready to embrace the extraordinary!' => 'اكتشفوا مجموعاتنا الجريئة الجديدة! أضيفوا إلى إطلالتكم تصاميم مميزة وألوانًا نابضة بالحياة. اختاروا نقوشًا وألوانًا جريئة لتجديد خزانة ملابسكم.',
                'Our Bold Collections are here to redefine your wardrobe with fearless designs and striking, vibrant colors. From daring patterns to powerful hues, this is your chance to break away from the ordinary and step into the extraordinary.' => 'تمنحكم مجموعاتنا الجريئة تصاميم مميزة وألوانًا لافتة لتجديد خزانة ملابسكم. اختاروا نقوشًا جريئة ودرجات نابضة بالحياة لإطلالة مختلفة.',
                'Unleash Your Boldness with Our New Collection!' => 'عبّروا عن جرأتكم مع مجموعتنا الجديدة!',
                'Get Ready for our new Bold Collections!' => 'استعدوا لاكتشاف مجموعاتنا الجريئة الجديدة!',
                'Women\'s Collections' => 'مجموعات النساء',
                'Men\'s Collections' => 'مجموعات الرجال',
                'Kid\'s Collection' => 'مجموعة الأطفال',
                'The game with our new additions!' => 'اكتشفوا تشكيلتنا الجديدة!',
                'Dedicated 24/7 support via chat and email' => 'دعم مخصص عبر المحادثة والبريد الإلكتروني على مدار الساعة',
                'No cost EMI available on all major credit cards' => 'تقسيط دون فوائد متاح مع معظم البطاقات الائتمانية',
                'Easy Product Replacement Available!' => 'إمكانية استبدال المنتجات بسهولة!',
                'Enjoy free shipping on all orders' => 'استفيدوا من التوصيل المجاني على جميع الطلبات',
                'Terms & Conditions' => 'الشروط والأحكام',
                'Terms of Use' => 'شروط الاستخدام',
                'Privacy Policy' => 'سياسة الخصوصية',
                'Customer Service' => 'خدمة العملاء',
                'Payment Policy' => 'سياسة الدفع',
                'Shipping Policy' => 'سياسة الشحن',
                'Refund Policy' => 'سياسة استرداد الأموال',
                'Return Policy' => 'سياسة الإرجاع',
                'What\'s New' => 'الجديد',
                'View Collections' => 'استكشفوا المجموعات',
                'Our Collections' => 'مجموعاتنا',
                'Free Shipping' => 'توصيل مجاني',
                'Product Replace' => 'استبدال المنتجات',
                'Emi Available' => 'تقسيط متاح',
                '24/7 Support' => 'دعم على مدار الساعة',
                'Book Tickets' => 'احجزوا',
                'About Us' => 'من نحن',
                'Contact Us' => 'اتصلوا بنا',
            ],
        ];

        foreach (DB::table('theme_customization_translations')->where('locale', 'en')->get() as $source) {
            foreach ($translations as $locale => $replacements) {
                uksort($replacements, fn ($left, $right) => strlen($right) <=> strlen($left));

                $options = json_decode($source->options, true) ?: [];

                array_walk_recursive($options, function (&$value) use ($replacements) {
                    if (is_string($value)) {
                        $value = str_replace(array_keys($replacements), array_values($replacements), $value);
                    }
                });

                DB::table('theme_customization_translations')->updateOrInsert([
                    'theme_customization_id' => $source->theme_customization_id,
                    'locale' => $locale,
                ], [
                    'options' => json_encode($options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
            }
        }
    }
}
