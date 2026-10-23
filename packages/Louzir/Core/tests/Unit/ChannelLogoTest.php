<?php

namespace Webkul\Core\Tests\Unit;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Storage;
use Webkul\Core\Facades\Core;
use Webkul\Core\Models\Channel;

class ChannelLogoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Core::shouldReceive('getConfigData')
            ->with('general.design.admin_logo.logo_image')
            ->andReturn(null)
            ->byDefault();
    }

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

    public function test_design_logo_takes_priority_over_channel_logo(): void
    {
        Storage::fake();
        Storage::put('configuration/site-logo.png', 'image');
        Storage::put('channels/custom.png', 'image');
        Core::shouldReceive('getConfigData')
            ->with('general.design.admin_logo.logo_image')
            ->andReturn('configuration/site-logo.png');

        $channel = new Channel;
        $channel->logo = 'channels/custom.png';

        $this->assertSame(Storage::url('configuration/site-logo.png'), $channel->logo_url);
    }

    public function test_missing_design_logo_falls_back_to_channel_logo(): void
    {
        Storage::fake();
        Storage::put('channels/custom.png', 'image');
        Core::shouldReceive('getConfigData')
            ->with('general.design.admin_logo.logo_image')
            ->andReturn('configuration/missing.png');

        $channel = new Channel;
        $channel->logo = 'channels/custom.png';

        $this->assertSame(Storage::url('channels/custom.png'), $channel->logo_url);

        $channel->logo = null;
        $this->assertSame(asset('assets/logo-auth-transparent.png'), $channel->logo_url);
    }
}
