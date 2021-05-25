<?php

namespace GetCandy\Api\Core\Assets\Models;

use App\Models\Store;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Assetable extends Pivot
{
    protected $table = 'assetables';

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
