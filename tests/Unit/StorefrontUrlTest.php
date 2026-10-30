<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webkul\Shop\Helpers\StorefrontUrl;

class StorefrontUrlTest extends TestCase
{
    public function test_installer_loopback_links_use_the_current_storefront(): void
    {
        foreach (['/page/about-us', '/contact-us', '/page/customer-service', '/page/whats-new', '/page/terms-of-use', '/page/terms-conditions'] as $path) {
            $this->assertSame($path, StorefrontUrl::normalize('http://localhost'.$path));
        }

        $this->assertSame('/page/about-us?locale=ar#contact', StorefrontUrl::normalize('https://localhost:8000/page/about-us?locale=ar#contact'));
        $this->assertSame('/contact-us', StorefrontUrl::normalize('http://127.0.0.1:8000/contact-us'));
        $this->assertSame('/contact-us', StorefrontUrl::normalize('http://[::1]/contact-us'));
        $this->assertSame('/contact-us', StorefrontUrl::normalize('//localhost/contact-us'));
        $this->assertSame('/', StorefrontUrl::normalize('http://localhost'));
        $this->assertSame('/example.com/page', StorefrontUrl::normalize('http://localhost//example.com/page'));
    }

    public function test_merchant_links_are_preserved(): void
    {
        foreach (['/page/about-us', 'https://example.com/page/about-us', 'https://localhost.example.com/page/about-us', 'mailto:contact@example.com', 'tel:+21625293194', '#contact'] as $url) {
            $this->assertSame($url, StorefrontUrl::normalize($url));
        }
    }
}
