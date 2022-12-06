<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Models;

use DI\Container;
use Illuminate\Database\Eloquent\Model as LaravelModel;
use UserFrosting\Sprinkle\Core\Database\Builder;
use UserFrosting\Sprinkle\Core\Database\Models\Concerns\HasRelationships;

/**
 * Model Class.
 *
 * UserFrosting's base data model, from which all UserFrosting data classes extend.
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
abstract class Model extends LaravelModel
{
    use HasRelationships;

    /**
     * @var Container The DI container for your application.
     *
     * Requires PHP-DI container specifically since "make" is used.
     */
    public static ?Container $ci = null;

    /**
     * Determine if an attribute exists on the model - even if it is null.
     *
     * @param string $key
     *
     * @return bool
     */
    public function attributeExists(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Determines whether a model exists by checking a unique column, including checking soft-deleted records.
     *
     * @param string $value
     * @param string $identifier
     * @param bool   $checkDeleted set to true to include soft-deleted records
     *
     * @return static|null
     */
    public static function findUnique(string $value, string $identifier, bool $checkDeleted = true): ?static
    {
        $query = self::whereRaw("LOWER($identifier) = ?", [mb_strtolower($value)]);

        // @phpstan-ignore-next-line hasMacro is available when $query is \Illuminate\Database\Eloquent\Builder
        if ($checkDeleted === true && (method_exists($query, 'withTrashed') || $query->hasMacro('withTrashed'))) {
            // @phpstan-ignore-next-line It's called through "hasMacro"
            $query = $query->withTrashed();
        }

        return $query->first();
    }

    /**
     * Determine if an relation exists on the model - even if it is null.
     *
     * @param string $key
     *
     * @return bool
     */
    public function relationExists(string $key): bool
    {
        return array_key_exists($key, $this->getRelations());
    }

    /**
     * Store the object in the DB, creating a new row if one doesn't already exist.
     *
     * Calls save(), then returns the id of the new record in the database.
     *
     * @return mixed the id of this object.
     */
    public function store(): mixed
    {
        $this->save();

        // Store function should always return the id of the object
        return $this->getKey();
    }

    /**
     * Overrides Laravel's base Model to return our custom _Query Builder_ object.
     * Use CI to resolve a query builder each time.
     *
     * @return Builder
     */
    protected function newBaseQueryBuilder()
    {
        /** @var Builder */
        return static::$ci?->make(Builder::class);
    }
}
