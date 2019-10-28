<?php

namespace DucCnzj\EsBuilder;

use Elasticsearch\ClientBuilder;
use DucCnzj\EsBuilder\Filters\Filter;
use DucCnzj\EsBuilder\Contracts\BuilderInterface;

trait Searchable
{
    public static function search(Filter $filter):BuilderInterface
    {
        return $filter->apply(app(BuilderInterface::class, ['model' => new static]));
    }

    public function searchableUsing()
    {
        $hosts = config('es.host', ['http://localhost:9200']);

        return ClientBuilder::create()
            ->setHosts($hosts)
            ->build();
    }
}
