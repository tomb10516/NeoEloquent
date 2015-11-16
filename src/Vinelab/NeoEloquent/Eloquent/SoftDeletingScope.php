<?php

namespace Vinelab\NeoEloquent\Eloquent;

use Illuminate\Database\Eloquent\Builder as IlluminateBuilder;
use Illuminate\Database\Eloquent\Model as IlluminateModel;

class SoftDeletingScope implements \Illuminate\Database\Eloquent\ScopeInterface
{
    /**
     * All of the extensions to be added to the builder.
     *
     * @var array
     */
    protected $extensions = ['ForceDelete', 'Restore', 'WithTrashed', 'OnlyTrashed'];

    /**
     * Add a WHERE statement with carried identifier to the query.
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     * @param string $boolean
     *
     * @return \Illuminate\Database\Query\Builder|static
     */
//    public function whereCarried($column, $operator = null, $value = null, $boolean = 'and')

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(IlluminateBuilder $builder, IlluminateModel $model)
    {
//        $foo = $builder->getQuery()->modelAsNode($model->getTable());
//        $builder->whereCarried($foo . "_" . $model->getQualifiedDeletedAtColumn(), "IS", "NULL");
//        $builder->carry([$foo . "." . $model->getQualifiedDeletedAtColumn() => $foo . "_" . $model->getQualifiedDeletedAtColumn()]);
//        $this->extend($builder);
//        $builder->whereCarried($model->getQualifiedDeletedAtColumn(), "IS", "NULL");
        $builder->whereSoftDeleted('node', $builder->getQuery()->modelAsNode($model->getTable()), $model->getQualifiedDeletedAtColumn(), 'and', true);
        $builder->carry([$model->getQualifiedDeletedAtColumn() => $model->getQualifiedDeletedAtColumn()]);
        $this->extend($builder);
    }

    /**
     * Remove the scope from the given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function remove(IlluminateBuilder $builder, IlluminateModel $model)
    {
        $column = $model->getQualifiedDeletedAtColumn();

        $query = $builder->getQuery();

        $query->wheres = collect($query->wheres)->reject(function ($where) use ($column) {
                return $this->isSoftDeleteConstraint($where, $column);
            })->values()->all();
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(IlluminateBuilder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }

        $builder->onDelete(function (Builder $builder) {
            $column = $this->getDeletedAtColumn($builder);

            return $builder->update([
                    $column => $builder->getModel()->freshTimestampString(),
            ]);
        });
    }

    /**
     * Get the "deleted at" column for the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return string
     */
    protected function getDeletedAtColumn(IlluminateBuilder $builder)
    {
        if (count($builder->getQuery()->joins) > 0) {
            return $builder->getModel()->getQualifiedDeletedAtColumn();
        } else {
            return $builder->getModel()->getDeletedAtColumn();
        }
    }

    /**
     * Add the force delete extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addForceDelete(IlluminateBuilder $builder)
    {
        $builder->macro('forceDelete', function (Builder $builder) {
            return $builder->getQuery()->delete();
        });
    }

    /**
     * Add the restore extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addRestore(IlluminateBuilder $builder)
    {
        $builder->macro('restore', function (Builder $builder) {
            $builder->withTrashed();

            return $builder->update([$builder->getModel()->getDeletedAtColumn() => null]);
        });
    }

    /**
     * Add the with-trashed extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addWithTrashed(IlluminateBuilder $builder)
    {
        $builder->macro('withTrashed', function (Builder $builder) {
            $this->remove($builder, $builder->getModel());

            return $builder;
        });
    }

    /**
     * Add the only-trashed extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addOnlyTrashed(IlluminateBuilder $builder)
    {
        $builder->macro('onlyTrashed', function (Builder $builder) {
            $model = $builder->getModel();

            $this->remove($builder, $model);

            $builder->getQuery()->whereNotNull($model->getQualifiedDeletedAtColumn());

            return $builder;
        });
    }

    /**
     * Determine if the given where clause is a soft delete constraint.
     *
     * @param  array   $where
     * @param  string  $column
     * @return bool
     */
    protected function isSoftDeleteConstraint(array $where, $column)
    {
        return $where['type'] == 'Null' && $where['column'] == $column;
    }
}
