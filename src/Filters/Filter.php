<?php

namespace DucCnzj\EsBuilder\Filters;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use DucCnzj\EsBuilder\Contracts\BuilderInterface;

/**
 * Class Filters
 * @package App\Filters
 */
abstract class Filter
{
    /**
     * @var bool
     */
    protected $usePrefix = false;

    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * @var Request|mixed
     */
    protected $request;

    /**
     * @var BuilderInterface|Builder
     */
    protected $builder;

    /**
     * @var array
     */
    protected $renames = [];

    /**
     * @var array
     */
    protected $filters = [];

    /**
     * Filters constructor.
     * @param Request|mixed $request
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * @param BuilderInterface $builder
     * @return BuilderInterface|Builder
     *
     * @author duc <1025434218@qq.com>
     */
    public function apply($builder)
    {
        $this->builder = $builder;

        foreach ($this->getFilters() as $key => $value) {
            $method = $this->resolveMethod($key);

            if (method_exists($this, $method)) {
                $this->{$method}($value);
            }
        }

        return $this->builder;
    }

    /**
     * @return array
     *
     * @author duc <1025434218@qq.com>
     */
    public function getFilters()
    {
        return array_filter($this->request->only($this->getKeys()), function ($item) {
            return $item === false || ! empty($item);
        });
    }

    /**
     * @return array
     *
     * @author duc <1025434218@qq.com>
     */
    private function getKeys(): array
    {
        $prefix = $this->usePrefix
            ? Str::endsWith($this->prefix, '_') ? $this->prefix : $this->prefix . '_'
            : null;

        return array_map(function ($key) use ($prefix) {
            if (in_array($key, array_keys($this->renames))) {
                return $this->renames[$key];
            }

            return $prefix . $key;
        }, $this->filters);
    }

    /**
     * @param array|mixed $fields
     * @return $this
     *
     * @author duc <1025434218@qq.com>
     */
    public function only($fields)
    {
        $fields = is_array($fields) ? $fields : func_get_args();

        $callback = function ($field) use ($fields) {
            return in_array($field, $fields);
        };

        $this->filters = array_filter($this->filters, $callback);

        return $this;
    }

    /**
     * @param string $prefix
     * @return $this
     *
     * @author duc <1025434218@qq.com>
     */
    public function withPrefix(string $prefix = '')
    {
        $this->prefix = $prefix ?: $this->prefix;
        $this->usePrefix = true;

        return $this;
    }

    /**
     * @param array $args
     * @return $this
     *
     * @author duc <1025434218@qq.com>
     */
    public function renameAs(...$args)
    {
        if (count($args) == 2 && in_array($args[0], $this->filters)) {
            $this->renames[$args[0]] = $args[1];
        }

        if (count($args) == 1 && is_array($args[0])) {
            foreach ($args[0] as $field => $as) {
                if (in_array($field, $this->filters)) {
                    $this->renames[$field] = $as;
                }
            }
        }

        return $this;
    }

    /**
     * @param $key
     * @return string
     *
     * @author duc <1025434218@qq.com>
     */
    protected function resolveMethod($key): string
    {
        if (in_array($key, $this->renames)) {
            return array_flip($this->renames)[$key];
        }

        $field = $this->usePrefix ? Str::after($key, $this->prefix) : $key;

        return Str::camel($field);
    }
}
