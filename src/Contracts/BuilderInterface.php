<?php


namespace DucCnzj\EsBuilder\Contracts;


use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface BuilderInterface
 * @package App\Services\ES
 */
interface BuilderInterface
{
    /**
     * @return mixed
     *
     * @author 神符 <1025434218@qq.com>
     */
    public function withTrashed ();

    /**
     * @param $relations
     * @return BuilderInterface
     *
     * @author 神符 <1025434218@qq.com>
     */
    public function with($relations);

    /**
     * @param string $column
     * @param null $operator
     * @param $value
     * @return BuilderInterface
     *
     * @author 神符 <1025434218@qq.com>
     */
    public function where(string $column, $operator = null, $value = null);

    /**
     * @param string $field
     * @param array $value
     * @return BuilderInterface
     *
     * @author 神符 <1025434218@qq.com>
     */
    public function whereNotIn(string $field, array $value);

    /**
     * @param string $field
     * @param array $value
     * @return BuilderInterface
     *
     * @author 神符 <1025434218@qq.com>
     */
    public function whereIn(string $field, array $value);

    /**
     * @param string $field
     * @param string $direction
     * @return BuilderInterface
     *
     * @author 神符 <1025434218@qq.com>
     */
    public function orderBy(string $field, $direction = 'asc');

    /**
     * @param array $columns
     * @return mixed
     *
     * @author 神符 <1025434218@qq.com>
     */
    public function first(array $columns = ['*']);

    /**
     * @param array $columns
     * @return Collection
     *
     * @author 神符 <1025434218@qq.com>
     */
    public function get(array $columns = ['*']);

    /**
     * @param int $perPage
     * @param array $columns
     * @param int $page
     * @return LengthAwarePaginator
     *
     * @author 神符 <1025434218@qq.com>
     */
    public function paginate(int $perPage = 15, $columns = ['*'], $page = 1);
}
