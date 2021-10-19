<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Util\ClassRepository;

use UserFrosting\Support\Exception\NotFoundException;

/**
 * Handle a PHP class repository.
 */
abstract class AbstractClassRepository implements ClassRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    abstract public function all(): array;

    /**
     * {@inheritDoc}
     */
    public function list(): array
    {
        return array_map(function ($m) {
            return get_class($m);
        }, $this->all());
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $class): object
    {
        if (!$this->has($class)) {
            throw new NotFoundException("Class `$class` not found.");
        }

        $results = array_filter($this->all(), function ($m) use ($class) {
            return get_class($m) === $class;
        });

        return array_values($results)[0]; // TODO : Test array_values with filter not being on key 0
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $class): bool
    {
        return in_array($class, $this->list());
    }

    // TODO : Add Iterator interface implemantion
    // https://www.php.net/manual/en/class.iterator.php
}
