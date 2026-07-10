<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Models\ShippingRate;
use Illuminate\Contracts\Validation\Validator;

trait ChecksShippingRateOverlap
{
    /**
     * Reject duplicate min/max weights and any intersecting weight range
     * against other rates in the same zone.
     */
    protected function checkForOverlappingRange(Validator $validator, $ignoreRateId): void
    {
        $zoneId = $this->input('shipping_zone_id');

        if (! $zoneId || $this->input('min_weight') === null) {
            return;
        }

        $minWeight = (float) $this->input('min_weight');
        $maxWeight = $this->filled('max_weight') ? (float) $this->input('max_weight') : null;

        $existingRates = ShippingRate::where('shipping_zone_id', $zoneId)
            ->when($ignoreRateId, fn ($query) => $query->where('id', '!=', $ignoreRateId))
            ->get();

        foreach ($existingRates as $existing) {
            $existingMin = (float) $existing->min_weight;
            $existingMax = $existing->max_weight !== null ? (float) $existing->max_weight : null;

            if (abs($existingMin - $minWeight) < 0.0001) {
                $validator->errors()->add('min_weight', 'Another rate in this zone already starts at this minimum weight.');

                continue;
            }

            if ($existingMax !== null && $maxWeight !== null && abs($existingMax - $maxWeight) < 0.0001) {
                $validator->errors()->add('max_weight', 'Another rate in this zone already ends at this maximum weight.');

                continue;
            }

            if ($existingMax === null && $maxWeight === null) {
                $validator->errors()->add('max_weight', 'Another rate in this zone is already open-ended (no maximum weight) — only one open-ended rate is allowed per zone.');

                continue;
            }

            $newUpper = $maxWeight ?? INF;
            $existingUpper = $existingMax ?? INF;

            if ($minWeight <= $existingUpper && $existingMin <= $newUpper) {
                $validator->errors()->add('min_weight', 'This weight range overlaps with an existing rate for this zone.');
            }
        }
    }
}
