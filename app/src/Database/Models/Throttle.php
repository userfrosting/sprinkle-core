<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Models;

/**
 * Throttle Class.
 *
 * Represents a throttleable request from a user agent.
 * 
 * @mixin \Illuminate\Database\Query\Builder
 *
 * @property string $type
 * @property string $ip
 * @property string $request_data
 */
class Throttle extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'throttles';

    protected $fillable = [
        'type',
        'ip',
        'request_data',
    ];

    /**
     * @var bool Enable timestamps for Throttles.
     */
    public $timestamps = true;
}
