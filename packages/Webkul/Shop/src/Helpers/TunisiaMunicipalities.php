<?php

namespace Webkul\Shop\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use UnexpectedValueException;

class TunisiaMunicipalities
{
    public function all(): array
    {
        $key = 'shop.tunisia-municipalities.v1';

        return Cache::remember($key, now()->addDay(), function () use ($key) {
            try {
                $data = Http::acceptJson()->connectTimeout(3)->timeout(15)
                    ->get('https://tn-municipality-api.vercel.app/api/municipalities')
                    ->throw()->json();

                if (! is_array($data) || ! array_is_list($data) || $data === []) {
                    throw new UnexpectedValueException('Invalid Tunisia municipality response');
                }

                $regions = $this->normalizeRegions($data);

                if ($regions === []) {
                    throw new UnexpectedValueException('Incomplete Tunisia municipality response');
                }

                Cache::forever($key.'.last-good', $regions);

                return $regions;
            } catch (\Throwable $exception) {
                report($exception);

                // Keep address entry available during an upstream outage.
                return Cache::get($key.'.last-good', []);
            }
        });
    }

    protected function normalizeRegions(array $data): array
    {
        $regions = [];

        foreach ($data as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            if (isset($entry['Delegations']) && is_array($entry['Delegations'])) {
                $regionName = trim((string) ($entry['Value'] ?? $entry['Name'] ?? ''));

                if ($regionName === '') {
                    continue;
                }

                $localities = [];

                foreach ($entry['Delegations'] as $place) {
                    if (! is_array($place)) {
                        continue;
                    }

                    $locality = $this->normalizeLocality($place);

                    if ($locality === null) {
                        continue;
                    }

                    $localities[$locality['name'].'|'.$locality['postcode']] = $locality;
                }

                $regions[] = [
                    'value' => $regionName,
                    'name' => trim((string) ($entry['Name'] ?? $regionName)),
                    'nameAr' => trim((string) ($entry['NameAr'] ?? $entry['Name'] ?? $regionName)),
                    'localities' => array_values($localities),
                ];

                continue;
            }

            $regionName = trim((string) ($entry['Value'] ?? $entry['Governorate'] ?? $entry['State'] ?? ''));

            if ($regionName === '') {
                continue;
            }

            $locality = $this->normalizeLocality($entry);

            if ($locality === null) {
                continue;
            }

            if (! isset($regions[$regionName])) {
                $regions[$regionName] = [
                    'value' => $regionName,
                    'name' => $regionName,
                    'nameAr' => trim((string) ($entry['ValueAr'] ?? $entry['NameAr'] ?? $regionName)),
                    'localities' => [],
                ];
            }

            $key = $locality['name'].'|'.$locality['postcode'];

            if (! isset($regions[$regionName]['localities'][$key])) {
                $regions[$regionName]['localities'][$key] = $locality;
            }
        }

        return array_values(array_map(function (array $region) {
            $region['localities'] = array_values($region['localities']);

            return $region;
        }, $regions));
    }

    protected function normalizeLocality(array $place): ?array
    {
        $name = trim((string) ($place['Name'] ?? ''));

        if ($name === '') {
            return null;
        }

        $postcode = trim((string) ($place['PostalCode'] ?? ''));

        if (! preg_match('/^\d{4}$/', $postcode)) {
            return null;
        }

        return [
            'name' => $name,
            'nameAr' => trim((string) ($place['NameAr'] ?? $name)),
            'postcode' => $postcode,
        ];
    }
}
