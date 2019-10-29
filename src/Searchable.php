<?php

namespace DucCnzj\EsBuilder;

use Elasticsearch\ClientBuilder;
use DucCnzj\EsBuilder\Filters\Filter;
use DucCnzj\EsBuilder\Traits\HasFilter;
use DucCnzj\EsBuilder\Contracts\BuilderInterface;

trait Searchable
{
    use HasFilter;

    /**
     * @param Filter|null $filter
     * @return BuilderInterface|\Illuminate\Database\Eloquent\Builder
     *
     * @author duc <1025434218@qq.com>
     */
    public static function search(Filter $filter = null)
    {
        $builder = app(BuilderInterface::class, ['model' => new static]);

        return is_null($filter) ? $builder : $filter->apply($builder);
    }

    /**
     * @return \Elasticsearch\Client
     *
     * @author duc <1025434218@qq.com>
     */
    public function searchableUsing()
    {
        $hosts = config('es.hosts', ['http://localhost:9200']);

        return ClientBuilder::create()
            ->setHosts($hosts)
            ->build();
    }
}
