<?php

namespace Repositories;

use App\Models\Province;
use Illuminate\Database\Eloquent\Collection;

class ProvinceRepository
{
    public function list(array $filters = []): Collection
    {
        return Province::select('id', 'name')->latest('id')->get();
    }
}
