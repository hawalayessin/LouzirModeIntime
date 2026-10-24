<?php

namespace Webkul\Core\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Webkul\Core\Eloquent\Repository;

class CoreConfigRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'Webkul\Core\Contracts\CoreConfig';
    }

    /**
     * Create core configuration.
     */
    public function create(array $data)
    {
        Event::dispatch('core.configuration.save.before');

        if (
            $data['locale']
            || $data['channel']
        ) {
            $locale = $data['locale'];
            $channel = $data['channel'];

            unset($data['locale']);
            unset($data['channel']);
        }

        $configuration = [];

        foreach ($data as $method => $fieldData) {
            $configuration += $this->recursiveArray($fieldData, $method);
        }

        $storedValues = $this->model->newQuery()
            ->whereIn('code', array_keys($configuration))
            ->get()
            ->groupBy('code');

        foreach ($configuration as $fieldName => $value) {
            $field = core()->getConfigField($fieldName);
            $channelBased = ! empty($field['channel_based']);
            $localeBased = ! empty($field['locale_based']);
            $records = ($storedValues->get($fieldName) ?? collect())
                ->filter(fn ($record) => (! $channelBased || $record->channel_code === $channel)
                    && (! $localeBased || $record->locale_code === $locale));

            if (is_array($value) && ! isset($value['delete'])) {
                $value = implode(',', $value);
            }

            $uploaded = request()->hasFile($fieldName);

            if ($uploaded) {
                $value = request()->file($fieldName)->store('configuration');
            }

            $attributes = [
                'code' => $fieldName,
                'value' => $value,
                'locale_code' => $localeBased ? $locale : null,
                'channel_code' => $channelBased ? $channel : null,
            ];

            if ($records->isEmpty()) {
                if (! is_array($value) || ! isset($value['delete'])) {
                    parent::create($attributes);
                }

                continue;
            }

            foreach ($records as $record) {
                if (isset($value['delete'])) {
                    parent::delete($record->id);

                    continue;
                }

                if ($uploaded && $record->value) {
                    Storage::delete($record->value);
                }

                $storedValue = $value === null ? null : (string) $value;

                if (! $uploaded && $record->value === $storedValue
                    && $record->locale_code === $attributes['locale_code']
                    && $record->channel_code === $attributes['channel_code']) {
                    continue;
                }

                parent::update($attributes, $record->id);
            }
        }

        Event::dispatch('core.configuration.save.after');
    }

    /**
     * Get the configuration title.
     */
    protected function getTranslatedTitle(mixed $configuration): string
    {
        if (
            method_exists($configuration, 'getTitle')
            && ! is_null($configuration->getTitle())
        ) {
            return trans($configuration->getTitle());
        }

        if (
            method_exists($configuration, 'getName')
            && ! is_null($configuration->getName())
        ) {
            return trans($configuration->getName());
        }

        return '';
    }

    /**
     * Get children and fields.
     */
    protected function getChildrenAndFields(mixed $configuration, string $searchTerm, array $path, array &$results): void
    {
        if (
            method_exists($configuration, 'getChildren')
            || method_exists($configuration, 'getFields')
        ) {
            $children = $configuration->haveChildren()
                ? $configuration->getChildren()
                : $configuration->getFields();

            $tempPath = array_merge($path, [[
                'key' => $configuration->getKey() ?? null,
                'title' => $this->getTranslatedTitle($configuration),
            ]]);

            $results = array_merge($results, $this->search($children, $searchTerm, $tempPath));
        }
    }

    /**
     * Search configuration.
     *
     * @param  array  $items
     */
    public function search(Collection $items, string $searchTerm, array $path = []): array
    {
        $results = [];

        foreach ($items as $configuration) {
            $title = $this->getTranslatedTitle($configuration);

            if (
                stripos($title, $searchTerm) !== false
                && count($path)
            ) {
                $queryParam = $path[1]['key'] ?? $configuration->getKey();

                $results[] = [
                    'title' => implode(' > ', [...Arr::pluck($path, 'title'), $title]),
                    'url' => route('admin.configuration.index', Str::replace('.', '/', $queryParam)),
                ];
            }

            $this->getChildrenAndFields($configuration, $searchTerm, $path, $results);
        }

        return $results;
    }

    /**
     * Recursive array.
     *
     * @return array
     */
    public function recursiveArray(array $formData, string $method, array &$data = [], array &$recursiveArrayData = [])
    {
        foreach ($formData as $form => $value) {
            $fieldName = $method.'.'.$form;

            if (is_array($value) && ! core()->getConfigField($fieldName)) {
                $this->recursiveArray($value, $fieldName, $data, $recursiveArrayData);
            } else {
                $recursiveArrayData[$fieldName] = $value;
            }
        }

        return $recursiveArrayData;
    }

    /**
     * Return dimension of the array.
     *
     * @param  array  $array
     * @return int
     */
    public function countDim($array)
    {
        return is_array(reset($array))
            ? $this->countDim(reset($array)) + 1
            : 1;
    }
}
