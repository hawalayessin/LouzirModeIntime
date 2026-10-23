<?php

namespace Webkul\Core\Tests\Unit;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Storage;
use Webkul\Core\Facades\Core;
use Webkul\Core\Models\Channel;

class ChannelFaviconTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake();
        Core::shouldReceive('getConfigData')
            ->with('general.design.admin_logo.favicon')->andReturn(null)->byDefault();
    }

    public function test_design_favicon_takes_priority(): void
    {
        Storage::put('configuration/site.ico', 'icon');
        Storage::put('channels/channel.ico', 'icon');
        Core::shouldReceive('getConfigData')
            ->with('general.design.admin_logo.favicon')->andReturn('configuration/site.ico');

        $channel = new Channel;
        $channel->favicon = 'channels/channel.ico';

        $this->assertSame(Storage::url('configuration/site.ico'), $channel->favicon_url);
    }

    public function test_missing_design_favicon_uses_channel_favicon(): void
    {
        Storage::put('channels/channel.png', 'icon');
        Core::shouldReceive('getConfigData')
            ->with('general.design.admin_logo.favicon')->andReturn('configuration/missing.ico');

        $channel = new Channel;
        $channel->favicon = 'channels/channel.png';

        $this->assertSame(Storage::url('channels/channel.png'), $channel->favicon_url);
    }

    public function test_absent_or_missing_favicons_use_the_bundled_icon(): void
    {
        $channel = new Channel;
        $this->assertSame(asset('assets/louzir.png'), $channel->favicon_url);

        $channel->favicon = 'channels/missing.png';
        $this->assertSame(asset('assets/louzir.png'), $channel->favicon_url);
    }
}
