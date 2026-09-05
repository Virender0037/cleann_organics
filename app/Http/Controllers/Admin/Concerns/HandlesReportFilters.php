<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Contracts\Database\Query\Builder as QueryBuilderContract;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Shared, lightweight filter handling for the admin reports (Phase K).
 *
 * Deliberately small — validation helpers + an inclusive date-range applier.
 * Not a report framework: each report controller still reads as a plain
 * controller with its own query.
 */
trait HandlesReportFilters
{
    /**
     * Validate the report's request. `from`/`to`/`search` rules are always
     * applied; each report passes the enum whitelists relevant to it so an
     * out-of-range status/method/id is rejected before touching the query.
     *
     * @param  array<string, array<int, string>>  $rules
     * @return array<string, mixed>
     */
    protected function validatedFilters(Request $request, array $rules = []): array
    {
        return $request->validate(array_merge([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'search' => ['nullable', 'string', 'max:255'],
        ], $rules));
    }

    /**
     * The effective [from, to] range as Carbon instants (start-of-day /
     * end-of-day, so the range is fully inclusive on a timestamp column).
     * An explicit request bound always wins; otherwise, when
     * $defaultToCurrentMonth is true and neither bound is given, the range
     * defaults to the current calendar month.
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    protected function dateRange(Request $request, bool $defaultToCurrentMonth): array
    {
        $from = $request->filled('from') ? $request->date('from')?->startOfDay() : null;
        $to = $request->filled('to') ? $request->date('to')?->endOfDay() : null;

        if ($from === null && $to === null && $defaultToCurrentMonth) {
            $from = Carbon::now()->startOfMonth();
            $to = Carbon::now()->endOfMonth();
        }

        return [$from, $to];
    }

    /**
     * Apply an inclusive date range to a query on the given timestamp
     * column. Uses `>= start` / `<= end` (index-friendly and inclusive)
     * rather than DATE() wrapping.
     *
     * @template TBuilder of EloquentBuilder|QueryBuilderContract
     *
     * @param  TBuilder  $query
     * @return TBuilder
     */
    protected function applyDateRange($query, ?Carbon $from, ?Carbon $to, string $column = 'created_at')
    {
        return $query
            ->when($from, fn ($q) => $q->where($column, '>=', $from))
            ->when($to, fn ($q) => $q->where($column, '<=', $to));
    }
}
