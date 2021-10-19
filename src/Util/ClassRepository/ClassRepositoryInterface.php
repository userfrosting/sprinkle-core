<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Util\ClassRepository;

/**
 * Handle a PHP class repository.
 */
interface ClassRepositoryInterface
{
    /**
     * Return all classes.
     *
     * @return object[] A list of classes instances.
     */
    public function all(): array;

    /**
     * Returns the same list as all, but as a list of class names.
     *
     * @return string[] A list class FQN.
     */
    public function list(): array;

    /**
     * Return the requested class instance from the repository.
     *
     * @param string $class Class FQN.
     *
     * @return object
     */
    public function get(string $class): object;

    /**
     * Validate if a specific class exist.
     *
     * @param string $class Class FQN.
     *
     * @return bool
     */
    public function has(string $class): bool;
}
