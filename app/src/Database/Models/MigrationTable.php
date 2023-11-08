<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Models;

/**
 * Migrations Model.
 *
 * @mixin \Illuminate\Database\Query\Builder
 *
 * Represents the migration table, containing the ran migrations.
 */
class MigrationTable extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'migrations';

    /**
     * @var bool Disable timestamps
     */
    public $timestamps = false;

    /**
     * @var string[] The attributes that are mass assignable.
     */
    protected $fillable = [
        'migration',
        'batch',
    ];
}
