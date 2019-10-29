<?php

namespace DucCnzj\EsBuilder\Traits;

use DucCnzj\EsBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait HasFilter
 *
 * @method static Builder filter($filter)
 *
 * @package App\Traits
 */
trait HasFilter
{
    public function scopeFilter($query, Filter $filters)
    {
        return $filters->apply($query);
    }
}
