<?php

namespace DucCnzj\EsBuilder;

use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use DucCnzj\EsBuilder\Contracts\BuilderInterface;

class Builder implements BuilderInterface
{
    protected $nullableDate = '1970-01-01T00:00:00+08:00';

    protected $operators = [
        '>'  => 'gt',
        '>=' => 'gte',
        '<'  => 'lt',
        '<=' => 'lte',
    ];

    protected $model;

    protected $with = [];

    protected $where = [];

    protected $whereNot = [];

    protected $sort = [];

    protected $withTrash = false;

    protected $source = [];

    protected $range = [];

    protected $regexp = [];

    protected $perPage;

    protected $page = 1;

    protected $paginate = true;

    protected $from = 0;

    protected $size = 15;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @param $relations
     * @return mixed
     *
     * @author 神符 <1025434218@qq.com>
     */
    public function with($relations)
    {
        $this->with = is_array($relations) ? $relations : func_get_args();

        return $this;
    }

    /**
     * @param string $field
     * @param null $operator
     * @param $value
     * @return BuilderInterface
     *
     * @author 神符 <1025434218@qq.com>
     */
    public function where(string $field, $operator = null, $value = null)
    {
        if (func_num_args() == 2) {
            $this->where = array_merge_recursive($this->where, [$field => is_array($operator) ? $operator : [$operator]]);

            return $this;
        }

        if ($this->invalidOperator($operator)) {
            $this->range[] = ['field' => $field, 'operator' => $operator, 'value' => Carbon::parse($value)->toIso8601String()];

            return $this;
        }

        if ($operator == '=') {
            $this->where = array_merge_recursive($this->where, [$field => is_array($value) ? $value : [$value]]);

            return $this;
        }

        if ($operator == '!=') {
            $this->whereNotIn($field, [$value]);

            return $this;
        }

        if (strtolower($operator ?? '') == 'like') {
            $value = str_replace('%', '.*', $value);
            $this->regexp[] = [$field => $value];

            return $this;
        }

        return $this;
    }

    /**
     * @param string $field
     * @param array $value
     * @return mixed
     *
     * @author 神符 <1025434218@qq.com>
     */
    public function whereNotIn(string $field, array $value)
    {
        $this->whereNot = array_merge_recursive($this->whereNot, [$field => $value]);

        return $this;
    }

    /**
     * @param string $field
     * @param array $value
     * @return mixed
     *
     * @author 神符 <1025434218@qq.com>
     */
    public function whereIn(string $field, array $value)
    {
        $this->where = array_merge_recursive($this->where, [$field => $value]);

        return $this;
    }

    /**
     * @param string $field
     * @param string $direction
     * @return mixed
     *
     * @author 神符 <1025434218@qq.com>
     */
    public function orderBy(string $field, $direction = 'asc')
    {
        $this->sort[] = [$field => $direction];

        return $this;
    }

    /**
     * @param array $columns
     * @return mixed
     *
     * @author 神符 <1025434218@qq.com>
     */
    public function get(array $columns = ['*'])
    {
        $this->paginate = false;

        $this->prepareAttributes();
        if ($columns != ['*']) {
            $this->source = $columns;
        }

        $res = $this->engine()->search($this->buildEsParams());
        $data = data_get($res, 'hits.hits.*._source', []);

        $class = get_class($this->model);

        /** @var Model $model */
        $model = new $class;
        $dates = $model->getDates();

        $collectData = collect($data)->map(function ($data) use ($dates) {
            foreach ($dates as $date) {
                if (isset($data[$date])) {
                    $data[$date] = Carbon::parse($data[$date]);
                }
            }

            return $data;
        })->toArray();

        $data = $model->hydrate($collectData);

        return $this->with ? $data->load($this->with) : $data;
    }

    /**
     * @param int $perPage
     * @param array $columns
     * @param int $page
     * @return LengthAwarePaginator
     *
     * @author 神符 <1025434218@qq.com>
     */
    public function paginate(int $perPage = 15, $columns = ['*'], $page = 1)
    {
        $this->perPage = $perPage;
        $this->page = $page;
        $this->prepareAttributes();
        if ($columns != ['*']) {
            $this->source = $columns;
        }

        $res = $this->engine()->search($this->buildEsParams());

        $items = data_get($res, 'hits.hits.*._source', []);
        $total = data_get($res, 'hits.total.value', 0);

        return (new LengthAwarePaginator($items, $total, $this->perPage, $this->page, [
            'path'     => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]));
    }

    /**
     * @return mixed
     *
     * @author 神符 <1025434218@qq.com>
     */
    private function engine()
    {
        return $this->model->searchableUsing();
    }

    public function __call($name, $arguments)
    {
        return $this;
    }

    private function invalidOperator(string $operator)
    {
        return in_array($operator, array_keys($this->operators));
    }

    private function buildEsParams()
    {
        if (! $this->paginate) {
            $this->from = 0;
            $this->size = config('es.track_total_hits', 10000);
        }

        if (method_exists($this->model, 'esIndex')) {
            $index = $this->model->esIndex();
        } else {
            $index = $this->model->getTable();
        }

        $params = [
            'index' => $index,
            'type'  => '_doc',
            'body'  => [
                'query' => [
                    'bool' => [
                        'must'     => array_merge($this->where, $this->range),
                        'must_not' => $this->whereNot,
                    ],
                ],
                'sort'  => $this->sort,
            ],
            'size'  => $this->size,
            'from'  => $this->from,
        ];

        if ($this->source) {
            $params['_source'] = $this->source;
        }

        return $params;
    }

    private function prepareAttributes()
    {
        $this->ensureTrash();
        $this->prepareWhere();
        $this->prepareWhereNot();
        $this->prepareOffset();
        $this->prepareRange();
    }

    private function prepareWhere()
    {
        $this->where = $this->prepare($this->where);
    }

    private function prepareOffset()
    {
        if (! $this->paginate) {
            return;
        }

        $this->size = $this->perPage;
        $this->from = $this->size *
            (
            $this->page > 1
                ? $this->page - 1
                : 0
            );
    }

    private function prepareWhereNot()
    {
        $this->whereNot = $this->prepare($this->whereNot);
    }

    private function prepareRange()
    {
        $ranges = collect($this->range ?? [])
            ->reduce(function ($carry, $item) {
                $data = [
                    $item['field'] => [
                        $this->operators[$item['operator']] => $item['value'],
                    ],
                ];

                return array_merge_recursive($carry ?? [], $data);
            });
        $this->range = collect($ranges)
            ->map(function ($item, $key) {
                return [
                    'range' => [$key => $item],
                ];
            })->values()->toArray();
    }

    private function ensureTrash()
    {
        if (! $this->withTrash) {
            $this->where['deleted_at'] = [
                $this->nullableDate(),
            ];
        } else {
            unset($this->where['deleted_at']);
        }
    }

    public function nullableDate()
    {
        if (method_exists($this->model, 'nullableDate')) {
            return $this->model->nullableDate();
        }

        return $this->nullableDate;
    }

    public function prepare(array $attributes)
    {
        return collect($attributes)
            ->map(function ($filter, $key) {
                $filter = array_unique($filter);
                if (count($filter) == 1) {
                    return [
                        'term' => [
                            $key => $filter[0],
                        ],
                    ];
                }

                if (count($filter) > 1) {
                    return [
                        'terms' => [
                            $key => $filter,
                        ],
                    ];
                }

                return [];
            })
            ->values()
            ->toArray();
    }
}
