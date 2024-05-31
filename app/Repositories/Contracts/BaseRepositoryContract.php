<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;

interface BaseRepositoryContract
{

    /**
     * Get first record
     *
     * @param array $where
     * @param array $relations
     * @param array $columns
     * @param bool $isResource
     * @return Model
     */

    public function getFirst(array $where = [], array $relations = [], array $columns = ['*'], bool $isResource = true);
    /**
     * Get all models.
     *
     * @param array $columns
     * @param array $relations
     * @param array $where
     * @return Collection
     */
    public function all(array $where = [], array $relations = [], array $columns = ['*']);

    /**
     * Get all trashed models.
     *
     * @return Collection
     */
    public function allTrashed(): Collection;

    /**
     * Find model by id.
     *
     * @param int $modelId
     * @param array $relations
     * @param array $columns
     * @param array $appends
     * @return Model
     */
    public function findById(int $modelId, array $relations = [], array $columns = ['*'], array $appends = []): ?Model;

    /**
     * Find trashed model by id.
     *
     * @param int $modelId
     * @param array $relations (optional) load model relations
     * @param array $columns (optional) select model columns
     * @return Model
     */
    public function findTrashedById(int $modelId, array $relations = [], array $columns = ['*'],): ?Model;

    /**
     * Find only trashed model by id.
     *
     * @param int $modelId
     * @return Model
     */
    public function findOnlyTrashedById(int $modelId): ?Model;


    /**
     * Update existing model.
     *
     * @param int $modelId
     * @param array $payload
     * @return Model
     */
    public function updateById(int $modelId, array $payload): ?Model;

    /**
     * Delete model by id.
     *
     * @param int $modelId
     * @return bool
     */
    public function deleteById(int $modelId): bool;

    /**
     * Restore model by id.
     *
     * @param int $modelId
     * @return bool
     */
    public function restoreById(int $modelId): bool;

    /**
     * Permanently delete model by id.
     *
     * @param int $modelId
     * @param array $relations
     * @return Model|JsonResource
     */
    public function permanentlyDeleteById(int $modelId, array $relations = []);


    /**
     * Store Model
     *
     * @param array $payload
     * @param bool $isResource
     * @return Model|JsonResource
     *
     */

    public function storeModel(array $payload, bool $isResource = true);
    /**
     * Show model
     *
     * @param Model $model
     * @param array $relations
     * @param bool $isResource
     * @return Model|JsonResource
     *
     */
    public function showModel(Model $model, array $relations = [], bool $isResource = true);

    /**
     * Update model
     *
     * @param Model $model
     * @param array $payload
     * @param bool $isResource
     * @return Model|JsonResource
     *
     */
    public function updateModel(Model $model, array $payload, bool $isResource = true);

    /**
     * Soft Delete
     *
     * @param Model $model
     * @return Model|JsonResource
     *
     */

    public function softDeleteModel(Model $model, bool $isResource = true);

    /**
     * Restore Model
     *
     * @param Model $model
     * @return Model|JsonResource
     *
     */

    public function restoreModel(Model $model, bool $isResource = true);

    /**
     * Delete Model
     *
     * @param Model $model
     * @param array $relations
     * @param string $mediaRelation optional Here is pass file/media data relation name
     * @return Model|JsonResource
     *
     */
    public function permanentlyDeleteModel(Model $model, array $relations = []);
}
