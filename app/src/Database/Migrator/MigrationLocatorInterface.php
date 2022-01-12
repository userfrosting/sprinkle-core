<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Migrator;

use UserFrosting\Sprinkle\Core\Database\MigrationInterface;
use UserFrosting\Sprinkle\Core\Util\ClassRepository\ClassRepositoryInterface;

/**
 * Find and returns all migrations definitions (classes) registered and available.
 *
 * @extends ClassRepositoryInterface<MigrationInterface>
 */
interface MigrationLocatorInterface extends ClassRepositoryInterface
{
    /**
     * Loop all the available sprinkles and return all available migrations across the whole app.
     *
     * @return MigrationInterface[] A list of all the migration instances found across every sprinkle
     */
    public function all(): array;

    /**
     * Returns the same as all, but as a list of class names.
     *
     * @return string[] A list of all the migration class found across every sprinkle
     */
    public function list(): array;

    /**
     * Return the migration class based on the migration string reference.
     *
     * @param string $migration Migration class as a string.
     *
     * @return MigrationInterface
     */
    public function get(string $migration): MigrationInterface;

    /**
     * Validate if a specific migration exist.
     *
     * @param string $migration Migration class as a string.
     *
     * @return bool
     */
    public function has(string $migration): bool;
}
