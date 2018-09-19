<?php

namespace Pensato\Api\Support;

interface RepositoryInterface
{
    public function all();

    public function list(int $page, int $size, array $relations = []);

    public function count();

    public function findItem($id, array $relations = [], string $useAsId = null);

    public function create(array $data);

    public function update(array $data, $id);

    public function delete($id);

    public function show($id);

    public function unguard();

}
