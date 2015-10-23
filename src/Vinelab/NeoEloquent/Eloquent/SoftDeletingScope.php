<?php namespace Vinelab\NeoEloquent\Eloquent;

class SoftDeletingScope extends \Illuminate\Database\Eloquent\SoftDeletingScope
{

    /**
     * 
     * This function is called when booting models that have the SoftDeletes trait.  We need to
     * override it because the underlying Eloquent adds a WHERE clause to automatically
     * exclude soft deleted nodes (WHERE foo.deleted_at IS NULL), however SQL like languages
     * do not require a WITH clause.  If we don't add foo.deleted_at to the Cypher WITH
     * clause, it will be filtered out of the MATCH before the WHERE clause, leading
     * to a Cypher error.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function apply(\Illuminate\Database\Eloquent\Builder $builder, \Illuminate\Database\Eloquent\Model $model)
    {
//        $builder->carry(["foo", "bar" => "quux"]);
//        $builder->whereNull($model->getQualifiedDeletedAtColumn());
//        $builder->with();
        $delCol =  $model->getQualifiedDeletedAtColumn();
        // BOOKMARK: get the right prefix here
        $prefix = "commentdel";
        
        $builder->carry([$prefix . "." . $delCol => $prefix . "_deleted_at"]);
        $builder->whereCarried($prefix . "_deleted_at", "IS", "NULL", 'and', true);

        $this->extend($builder);
    }
}
