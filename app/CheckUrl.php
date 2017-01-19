<?php

namespace REBELinBLUE\Deployer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Lang;
use REBELinBLUE\Deployer\Jobs\RequestProjectCheckUrl;
use REBELinBLUE\Deployer\Traits\BroadcastChanges;

/**
 * The application's url store for health check.
 */
class CheckUrl extends Model
{
    use SoftDeletes, BroadcastChanges, DispatchesJobs;

    const ONLINE   = 0;
    const UNTESTED = 1;
    const OFFLINE  = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'url', 'project_id', 'period'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at', 'deleted_at', 'pivot'];

    /**
     * The fields which should be treated as Carbon instances.
     *
     * @var array
     */
    protected $dates = ['last_seen'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'         => 'integer',
        'project_id' => 'integer',
        'missed'     => 'integer',
        'period'     => 'integer',
        'status'     => 'integer',
    ];

    /**
     * Override the boot method to bind model event listeners.
     * @dispatches RequestProjectCheckUrl
     */
    public static function boot()
    {
        parent::boot();

        // When saving the model, if the URL has changed we need to test it
        static::saved(function (CheckUrl $model) {
            if ($model->status === self::UNTESTED) {
                $model->dispatch(new RequestProjectCheckUrl([$model]));
            }
        });
    }

    /**
     * Define a mutator to set the status to untested if the URL changes.
     *
     * @param string $value
     */
    public function setUrlAttribute($value)
    {
        if (!array_key_exists('url', $this->attributes) || $value !== $this->attributes['url']) {
            $this->attributes['status'] = self::UNTESTED;
            $this->attributes['last_seen'] = null;
        }

        $this->attributes['url'] = $value;
    }

    /**
     * Belongs to relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Determines whether the URL is currently online.
     *
     * @return bool
     */
    public function isHealthy()
    {
        return ($this->status === self::ONLINE);
    }
}
