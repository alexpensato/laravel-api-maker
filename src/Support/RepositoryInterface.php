<?php

namespace Pensato\Api\Support;

interface RepositoryInterface
{
    public function all();

    public function list(int $page, int $size, array $relations = [], array $volatileFields = [], array $filters = [], array $orderBy = [], SoftDeletesPolicyEnum $softDeletesPolicy = null);

    public function count();

    public function findItem($id, array $relations = [], string $useAsId = null);

    public function create(array $data);

    public function update(array $data, $id);

    public function delete($id);

    public function show($id);

    public function associate($class, $id, $ids, $fieldName);

    public function attach($relation, $id, $ids);

    public function detach($relation, $id, $ids);
}
