<?php

namespace Shopmade\Invoicing\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

/**
 * Quotes and invoices can point at a job when the shopmade-jobs package is installed. Without it the job field,
 * the "Linked job" card and the job relation stay out of the way.
 */
final class JobLink
{
    public const string MODEL = 'Shopmade\\Jobs\\Models\\Job';

    public static function installed(): bool
    {
        return class_exists(self::MODEL);
    }

    /**
     * @param  list<string>  $relations
     * @return list<string>
     */
    public static function with(array $relations): array
    {
        return self::installed() ? [...$relations, 'job'] : $relations;
    }

    /**
     * Jobs to offer in a quote or invoice form, newest first.
     */
    public static function options(): Collection
    {
        if (! self::installed()) {
            return new Collection;
        }

        /** @var Builder $query */
        $query = (self::MODEL)::query();

        return $query->with('customer')->orderByDesc('scheduled_at')->get();
    }

    /**
     * The job must exist and belong to the same customer as the quote or invoice.
     *
     * @return list<mixed>
     */
    public static function rules(int $customerId): array
    {
        return self::installed()
            ? ['nullable', Rule::exists('jobs', 'id')->where('customer_id', $customerId)]
            : ['prohibited'];
    }
}
