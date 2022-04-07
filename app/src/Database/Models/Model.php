<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Models;

use Illuminate\Database\Eloquent\Model as LaravelModel;
use Psr\Container\ContainerInterface;
use UserFrosting\Sprinkle\Core\Database\Builder;
use UserFrosting\Sprinkle\Core\Database\Models\Concerns\HasRelationships;

/**
 * Model Class.
 *
 * UserFrosting's base data model, from which all UserFrosting data classes extend.
 */
abstract class Model extends LaravelModel
{
    use HasRelationships;

    /**
     * @var ContainerInterface The DI container for your application.
     */
    public static ?ContainerInterface $ci = null;

    /**
     * Determine if an attribute exists on the model - even if it is null.
     *
     * @param string $key
     *
     * @return bool
     */
    public function attributeExists($key)
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Determines whether a model exists by checking a unique column, including checking soft-deleted records.
     *
     * @param mixed  $value
     * @param string $identifier
     * @param bool   $checkDeleted set to true to include soft-deleted records
     *
     * @return \UserFrosting\Sprinkle\Core\Database\Models\Model|null
     */
    public static function findUnique($value, $identifier, $checkDeleted = true)
    {
        $query = static::whereRaw("LOWER($identifier) = ?", [mb_strtolower($value)]);

        if ($checkDeleted && method_exists($query, 'withTrashed')) {
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
    public function relationExists($key)
    {
        return array_key_exists($key, $this->relations);
    }

    /**
     * Store the object in the DB, creating a new row if one doesn't already exist.
     *
     * Calls save(), then returns the id of the new record in the database.
     *
     * @return int the id of this object.
     */
    public function store()
    {
        $this->save();

        // Store function should always return the id of the object
        return $this->id;
    }

    /**
     * Overrides Laravel's base Model to return our custom query builder object.
     *
     * @return Builder
     */
    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();

        // TODO : To keep classmapper feature here, it would be the next line, But need the $ci... And I don't like the way it was done (in event)
        // Ci would replace classmapper here, but it would need to be injected, so created by the container... always... A Trait would be better...
        // So the class is hardcoded for now
        // Would be:
        // return $ci->make('UserFrosting\Sprinkle\Core\Database\Builder', [
        //     $connection,
        //     $connection->getQueryGrammar(),
        //     $connection->getPostProcessor()
        // ]);

        return new Builder(
            $connection,
            $connection->getQueryGrammar(),
            $connection->getPostProcessor()
        );
    }
}
