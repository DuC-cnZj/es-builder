<?php

namespace DucCnzj\EsBuilder;

use Elasticsearch\ClientBuilder;
use DucCnzj\EsBuilder\Filters\Filter;
use DucCnzj\EsBuilder\Contracts\BuilderInterface;

trait Searchable
{
    public static function search(Filter $filter = null):BuilderInterface
    {
        $builder = app(BuilderInterface::class, ['model' => new static]);
        
        return is_null($filter) ? $builder : $filter->apply($builder);
    }

    public function searchableUsing()
    {
        $hosts = config('es.hosts', ['http://localhost:9200']);

        return ClientBuilder::create()
            ->setHosts($hosts)
            ->build();
    }
}
