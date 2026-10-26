<?php

namespace Webkul\Core;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Webkul\Core\Models\CoreConfig;
use Webkul\Core\Repositories\CoreConfigRepository;
use Webkul\Core\SystemConfig\Item;

class SystemConfig
{
    /**
     * Items array.
     */
    public array $items = [];

    private ?array $fieldsByName = null;

    /**
     * The configuration item keys that should not be shown in the admin system settings UI.
     */
    protected array $hiddenConfigKeywords = [
        'analytics',
        'diagnostic',
        'diagnostics',
        'metric',
        'metrics',
        'reporting',
        'reports',
    ];

    /**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct(protected CoreConfigRepository $coreConfigRepository) {}

    /**
     * Add Item.
     */
    public function addItem(Item $item): void
    {
        $this->items[] = $item;
    }

    /**
     * Get all configuration items.
     */
    public function getItems(): Collection
    {
        if (! $this->items) {
            $this->prepareConfigurationItems();
        }

        return collect($this->items)
            ->filter(fn (Item $item) => ! $this->shouldHideConfigurationItem($item->getKey(), $item->getName()))
            ->sortBy('sort');
    }

    /**
     * Determine if an item should be hidden from the admin system settings listing.
     */
    protected function shouldHideConfigurationItem(string $key, ?string $name = null): bool
    {
        $haystack = strtolower(implode(' ', array_filter([
            $key,
            $name,
        ], fn ($value) => is_string($value) && $value !== '')));

        foreach ($this->hiddenConfigKeywords as $keyword) {
            if (str_contains($haystack, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve Core Config
     */
    private function retrieveCoreConfig(): array
    {
        static $items;

        if ($items) {
            return $items;
        }

        return $items = config('core');
    }

    /**
     * Prepare configuration items.
     */
    public function prepareConfigurationItems()
    {
        $configWithDotNotation = [];

        foreach ($this->retrieveCoreConfig() as $item) {
            $configWithDotNotation[$item['key']] = $item;
        }

        $configs = Arr::undot(Arr::dot($configWithDotNotation));

        foreach ($configs as $configItem) {
            $subConfigItems = $this->processSubConfigItems($configItem);

            $this->addItem(new Item(
                children: $subConfigItems,
                fields: $configItem['fields'] ?? null,
                icon: $configItem['icon'] ?? null,
                icon_class: $configItem['icon_class'] ?? null,
                info: trans($configItem['info']) ?? null,
                key: $configItem['key'],
                name: trans($configItem['name']),
                route: $configItem['route'] ?? null,
                sort: $configItem['sort'],
            ));
        }
    }

    /**
     * Process sub config items.
     */
    private function processSubConfigItems($configItem): Collection
    {
        return collect($configItem)
            ->sortBy('sort')
            ->filter(fn ($value) => is_array($value)
                && isset($value['name'])
                && ! $this->shouldHideConfigurationItem($value['key'] ?? '', $value['name'] ?? ''))
            ->map(function ($subConfigItem) {
                $configItemChildren = $this->processSubConfigItems($subConfigItem);

                return new Item(
                    children: $configItemChildren,
                    fields: $subConfigItem['fields'] ?? null,
                    icon: $subConfigItem['icon'] ?? null,
                    icon_class: $subConfigItem['icon_class'] ?? null,
                    info: trans($subConfigItem['info']) ?? null,
                    key: $subConfigItem['key'],
                    name: trans($subConfigItem['name']),
                    route: $subConfigItem['route'] ?? null,
                    sort: $subConfigItem['sort'] ?? null,
                );
            });
    }

    /**
     * Get active configuration item.
     */
    public function getActiveConfigurationItem(): ?Item
    {
        if (! $slug = request()->route('slug')) {
            return null;
        }

        $activeItem = $this->getItems()->where('key', $slug)->first() ?? null;

        if (! $activeItem) {
            return null;
        }

        if ($slug2 = request()->route('slug2')) {
            $activeItem = $activeItem->getChildren()[$slug2];
        }

        return $activeItem;
    }

    /**
     * Get config field.
     */
    public function getConfigField(string $fieldName): ?array
    {
        if ($this->fieldsByName === null) {
            $this->fieldsByName = [];

            foreach ($this->retrieveCoreConfig() as $section) {
                foreach ($section['fields'] ?? [] as $field) {
                    $this->fieldsByName[$section['key'].'.'.$field['name']] ??= $field;
                }
            }
        }

        return $this->fieldsByName[$fieldName] ?? null;
    }

    /**
     * Get core config values.
     */
    protected function getCoreConfig(string $field, ?string $channel, ?string $locale): ?CoreConfig
    {
        $fields = $this->getConfigField($field);

        if (! empty($fields['channel_based'])) {
            if (! empty($fields['locale_based'])) {
                $coreConfigValue = $this->coreConfigRepository->findOneWhere([
                    'code' => $field,
                    'channel_code' => $channel,
                    'locale_code' => $locale,
                ]);
            } else {
                $coreConfigValue = $this->coreConfigRepository->findOneWhere([
                    'code' => $field,
                    'channel_code' => $channel,
                ]);
            }
        } else {
            if (! empty($fields['locale_based'])) {
                $coreConfigValue = $this->coreConfigRepository->findOneWhere([
                    'code' => $field,
                    'locale_code' => $locale,
                ]);
            } else {
                $coreConfigValue = $this->coreConfigRepository->findOneWhere([
                    'code' => $field,
                ]);
            }
        }

        return $coreConfigValue;
    }

    /**
     * Get default config.
     */
    protected function getDefaultConfig(string $field): mixed
    {
        $configFieldInfo = $this->getConfigField($field);

        $fields = explode('.', $field);

        array_shift($fields);

        $field = implode('.', $fields);

        return Config::get($field, $configFieldInfo['default'] ?? null);
    }

    /**
     * Get the config data.
     */
    public function getConfigData(string $field, ?string $currentChannelCode = null, ?string $currentLocaleCode = null): mixed
    {
        if (empty($currentChannelCode)) {
            $currentChannelCode = core()->getRequestedChannelCode();
        }

        if (empty($currentLocaleCode)) {
            $currentLocaleCode = core()->getRequestedLocaleCode();
        }

        $memoize = request()->attributes->get('core.config.memoize', false);
        $cacheKey = json_encode([$field, $currentChannelCode, $currentLocaleCode], JSON_THROW_ON_ERROR);
        $values = $memoize ? request()->attributes->get('core.config.values', []) : [];
        if (array_key_exists($cacheKey, $values)) {
            return $values[$cacheKey];
        }

        $coreConfig = $this->getCoreConfig($field, $currentChannelCode, $currentLocaleCode);
        $value = $coreConfig ? $coreConfig->value : $this->getDefaultConfig($field);
        if ($memoize) {
            $values[$cacheKey] = $value;
            request()->attributes->set('core.config.values', $values);
        }

        return $value;
    }

    /** Opt in only for read-only endpoints. The request owns the snapshot, including under Octane. */
    public function memoizeForRequest(): void
    {
        request()->attributes->set('core.config.memoize', true);
    }
}
