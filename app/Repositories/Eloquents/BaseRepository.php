<?php

namespace App\Repositories\Eloquents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Contracts\BaseRepositoryContract;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseRepository implements BaseRepositoryContract
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @jsonResource string
     */
    protected $resource;

    /**
     * BaseRepository constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model, string $resource = null)
    {
        $this->model = $model;
        $this->resource = $resource;
    }

    /**
     * Get first record
     *
     * @param array $columns
     * @param array $relations
     * @param array $where
     * @return Model
     */
    public function getFirst(array $where = [], array $relations = [], array $columns = ['*'], bool $isResource = true)
    {
        $model = $this->model
            ->when($columns, function ($q, $columns) {
                $q->select($columns);
            })
            ->when($relations, function ($q, $relations) {
                $q->with($relations);
            })
            ->when($where, function ($q, $where) {
                $q->where($where);
            })->firstOrFail();


        return  $this->resource && $isResource ? $this->resource::make($model) : $model;
    }

    /**
     * @param array $where
     * @param array $relations
     * @param array $columns
     * @return Collection
     */
    public function all(array $where = [], array $relations = [], array $columns = ['*'])
    {
        $modelData = $this->model
            ->when($columns, function ($q, $columns) {
                $q->select($columns);
            })
            ->when($relations, function ($q, $relations) {
                $q->with($relations);
            })
            ->when($where, function ($q, $where) {
                $q->where($where);
            })
            ->get();
        return $this->resource ? $this->resource::collection($modelData) : $modelData;
    }

    /**
     * Get all trashed models.
     *
     * @return Collection
     */
    public function allTrashed(): Collection
    {
        return $this->model->onlyTrashed()->get();
    }

    /**
     * Find model by id.
     *
     * @param int $modelId
     * @param array $relations
     * @param array $columns
     * @param array $appends
     * @return Model
     */
    public function findById(int $modelId, array $relations = [], array $columns = ['*'], array $appends = []): ?Model
    {
        return $this->model
            ->when($columns, function ($q) use ($columns) {
                $q->select($columns);
            })->when($relations, function ($q) use ($relations) {
                $q->with($relations);
            })->findOrFail($modelId)->append($appends);
    }

    /**
     * Find trashed model by id.
     *
     * @param int $modelId
     * @param array $relations (optional) load model relations
     * @param array $columns (optional) select model columns
     * @return Model
     */
    public function findTrashedById(int $modelId, array $relations = [], array $columns = ['*'],): ?Model
    {
        return $this->model
            ->withTrashed()
            ->when($columns, function ($q, $columns) {
                $q->select($columns);
            })
            ->when($relations, function ($q, $relations) {
                $q->with($relations);
            })
            ->findOrFail($modelId);
    }

    /**
     * Find only trashed model by id.
     *
     * @param int $modelId
     * @return Model
     */
    public function findOnlyTrashedById(int $modelId): ?Model
    {
        return $this->model->onlyTrashed()->findOrFail($modelId);
    }


    /**
     * Update existing model.
     *
     * @param int $modelId
     * @param array $payload
     * @return Model
     */
    public function updateById(int $modelId, array $payload): ?Model
    {
        $model = $this->findById($modelId);

        return $this->updateModel($model, $payload, false);
    }

    /**
     * Delete model by id.
     *
     * @param int $modelId
     * @return bool
     */
    public function deleteById(int $modelId): bool
    {
        return $this->findById($modelId)->delete();
    }

    /**
     * Restore model by id.
     *
     * @param int $modelId
     * @return bool
     */
    public function restoreById(int $modelId): bool
    {
        return $this->findOnlyTrashedById($modelId)->restore();
    }

    /**
     * Permanently delete model by id.
     *
     * @param int $modelId
     * @param array $relations
     * @return Model|JsonResource
     */
    public function permanentlyDeleteById(int $modelId, array $relations = [])
    {
        if (method_exists($this->model, 'trashed')) {
            $model = $this->findTrashedById($modelId);
        } else {
            $model = $this->findById($modelId);
        }

        return $this->permanentlyDeleteModel($model, $relations);
    }

    /**
     * delete child relation records
     *
     * @param object $relation
     * @return bool
     */
    protected function deleteChildRecords($relation)
    {
        // delete nested relations
        if (method_exists($relation, "getRelations")) {
            foreach ($relation->getRelations() as $key => $nestedRelation) {
                $this->deleteChildRecords($nestedRelation);
            }
        }

        $response = false;

        // is collection
        if ($relation instanceof Collection) {

            // has data
            if ($relation->count() > 0) {
                foreach ($relation as $object) {
                    if (method_exists($object, 'trashed')) {
                        $object->forceDelete();
                    } else {
                        $object->delete();
                    }
                }
                return true;
            }
        } else {
            if (method_exists($relation, 'trashed')) {
                $response =  $relation->forceDelete();
            } else {
                $response =  $relation->delete();
            }
        }
        return  $response;
    }

    /**
     * Store Model
     *
     * @param array $payload
     * @return Model|JsonResource
     *
     */

    public function storeModel(array $payload, bool $isResource = true)
    {
        $model = $this->model;
        if (method_exists($model, "toFill")) {
            $model->toFill($payload);
        } else {
            $model->fill($payload);
        }
        $model->save();

        return $this->resource && $isResource ? $this->resource::make($model) : $model;
    }

    /**
     * Show model
     * @param Model $model
     * @param array $relations
     * @return Model|JsonResource
     *
     */
    public function showModel(Model $model, array $relations = [], bool $isResource = true)
    {
        $model->load($relations);
        return $this->resource && $isResource ? $this->resource::make($model) : $model;
    }

    /**
     * Update model
     * @param Model $model
     * @param array $payload
     * @return Model|JsonResource
     *
     */
    public function updateModel(Model $model, array $payload, bool $isResource = true)
    {
        if (method_exists($model, "toFill")) {
            $model->toFill($payload);
        } else {
            $model->fill($payload);
        }
        $model->save();

        $model->fresh();
        return $this->resource && $isResource ? $this->resource::make($model) : $model;
    }

    /**
     * Restore Model
     *
     * @param Model $model
     * @return Model|JsonResource
     *
     */
    public function restoreModel(Model $model, bool $isResource = true)
    {
        $model->restore();
        return $this->resource && $isResource ? $this->resource::make($model) : $model;
    }

    /**
     * Soft Delete Model
     *
     * @param Model $model
     * @return Model|JsonResource
     *
     */
    public function softDeleteModel(Model $model, bool $isResource = true)
    {
        $model->delete();
        return $this->resource && $isResource ? $this->resource::make($model) : $model;
    }

    /**
     * Delete Model
     *
     * @param Model $model
     * @param array $relations
     * @return Model|JsonResource
     *
     */
    public function permanentlyDeleteModel(
        Model $model,
        array $relations = []
    ) {

        $model->load($relations);

        // delete nested relations
        if (method_exists($model, "getRelations")) {

            foreach ($model->getRelations() as $key => $relation) {
                $this->deleteChildRecords($relation);
            }
        }



        // delete parent model
        if (method_exists($model, 'trashed')) {
            $model->forceDelete();
        } else {
            $model->delete();
        }

        return $this->resource ? $this->resource::make($model) : $model;
    }
}
