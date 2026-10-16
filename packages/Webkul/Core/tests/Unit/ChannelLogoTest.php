<?php

namespace Webkul\Core\Tests\Unit;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Storage;
use Webkul\Core\Models\Channel;

class ChannelLogoTest extends TestCase
{
    public function test_bundled_logo_is_used_for_absent_or_missing_uploads(): void
    {
        Storage::fake();
        $channel = new Channel;
        $this->assertSame(asset('assets/logo-auth-transparent.png'), $channel->logo_url);
        $channel->logo = 'channels/missing.png';
        $this->assertSame(asset('assets/logo-auth-transparent.png'), $channel->logo_url);
    }

    public function test_existing_uploaded_logo_is_preserved(): void
    {
        Storage::fake();
        Storage::put('channels/custom.png', 'image');
        $channel = new Channel;
        $channel->logo = 'channels/custom.png';
        $this->assertSame(Storage::url('channels/custom.png'), $channel->logo_url);
    }
}
