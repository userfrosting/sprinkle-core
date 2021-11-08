<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Seeder;

use UserFrosting\Sprinkle\Core\Util\ClassRepository\ClassRepositoryInterface;

/**
 * Find and returns all database seeds (classes) registered and available.
 */
interface SeedRepositoryInterface extends ClassRepositoryInterface
{
    /**
     * Loop all the available sprinkles and return all available seeds across the whole app.
     *
     * @return SeedInterface[] A list of all the seeds found across every sprinkle
     */
    public function all(): array;

    /**
     * Returns the same as all, but as a list of class names.
     *
     * @return string[] A list of all the migration class found across every sprinkle
     */
    public function list(): array;

    /**
     * Return the seed class (instance) based on the class name (string).
     *
     * @param string $seed Seed class name as a string.
     *
     * @return SeedInterface
     */
    public function get(string $seed): SeedInterface;

    /**
     * Validate if a specific seed exist.
     *
     * @param string $seed Seed class as a string.
     *
     * @return bool
     */
    public function has(string $seed): bool;
}
