<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Migrator;

use UserFrosting\Sprinkle\Core\Database\MigrationInterface;

/**
 * Find and returns all migrations.
 */
interface MigrationLocatorInterface
{
    /**
     * Loop all the available sprinkles and return all available migrations across the whole app.
     *
     * @return MigrationInterface[] A list of all the migration instances found across every sprinkle
     */
    public function all(): array;

    /**
     * Returns the same as getAll, but as a list of class names for management by the analyser.
     *
     * @return string[] A list of all the migration class found across every sprinkle
     */
    public function list(): array;

    /**
     * Return the migration class based on the migration string reference.
     *
     * @param string $migration Migration class as a string, as saved in the log probably.
     *
     * @return MigrationInterface
     */
    public function get(string $migration): MigrationInterface;

    /**
     * Validate if a specific migration exist.
     *
     * @param string $migration Migration class as a string, as saved in the log probably.
     *
     * @return bool
     */
    public function has(string $migration): bool;
}
