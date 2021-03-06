<?php

namespace GetCandy\Api\Core\Scaffold;

use App\Http\Scopes\StoreScope;
use App\Models\Store;
use GetCandy;
use GetCandy\Api\Core\Routes\Models\Route;
use GetCandy\Api\Core\Traits\Hashids;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    use Hashids;

    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'main';

    public $custom_attributes = [];

    public function getSettingsAttribute()
    {
        $settings = GetCandy::settings()->get($this->settings);
        if (! $settings) {
            return [];
        }

        return $settings->content;
    }

    public function setCustomAttribute($key, $value)
    {
        $this->custom_attributes[$key] = $value;

        return $this;
    }

    public function getCustomAttribute($key)
    {
        return $this->custom_attributes[$key] ?? null;
    }

    /**
     * Scope a query to only include enabled.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', '=', true);
    }

//    public function scopeStore($query)
//    {
//        return $query->where('store_id','=',\Illuminate\Support\Facades\Session::get('store_id'));
//    }

    /**
     * Scope a query to only include the default record.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefault($query)
    {
        return $query->where('default', '=', true);
    }

    public function routes()
    {
        return $this->morphMany(Route::class, 'element');
    }


    public function merchant_store()
    {
        return $this->belongsTo(Store::class);
    }


    protected static function boot()
    {
        parent::boot();
        self::creating(function($model){
            if(!is_null(auth()->user())) {
                $model->store_id = auth()
                        ->user()
                        ->merchant_store()
                        ->where('default_store', '=', 1)
                        ->first()->id ?? \Illuminate\Support\Facades\Session::get('store_id');
            }
        });
        self::addGlobalScope(new StoreScope());
    }

    /**
     * Determine if the given relationship (method) exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasRelation($key)
    {
        // If the key already exists in the relationships array, it just means the
        // relationship has already been loaded, so we'll just return it out of
        // here because there is no need to query within the relations twice.
        if ($this->relationLoaded($key)) {
            return true;
        }

        // If the "attribute" exists as a method on the model, we will just assume
        // it is a relationship and will load and return results from the query
        // and hydrate the relationship's value on the "relationships" array.
        if (method_exists($this, $key)) {
            //Uses PHP built in function to determine whether the returned object is a laravel relation
            return is_a($this->$key(), "Illuminate\Database\Eloquent\Relations\Relation");
        }

        return false;
    }
}
