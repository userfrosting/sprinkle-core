<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database;

/**
 * Migration Interfaces class.
 */
interface MigrationInterface
{
    /**
     * Method to apply changes to the database.
     */
    // TODO : Return type ?
    public function up();

    /**
     * Method to revert changes applied by the `up` method.
     */
    // TODO : Return type ?
    public function down();
}
