<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Database\Migrator;

use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationDependencyAnalyser;
use UserFrosting\Sprinkle\Core\Util\BadClassNameException;

// TODO : Need whole rework of analyse test
class MigrationDependencyAnalyserTest extends TestCase
{
    /*public function testAnalyser()
    {
        // TODO : Integration bad, Unit better. Maybe mock or Stub ?
        $migrations = [
            'UserFrosting\\Tests\\Unit\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Unit\\Migrations\\one\\CreatePasswordResetsTable',
        ];

        $expected = [
            '\\UserFrosting\\Tests\\Unit\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Unit\\Migrations\\one\\CreatePasswordResetsTable',
        ];

        $analyser = new MigrationDependencyAnalyser($migrations, []);

        $this->assertEquals($expected, $analyser->getFulfillable());
        $this->assertEquals([], $analyser->getUnfulfillable());
    }

    public function testAnalyserWithInvalidClass()
    {
        $migrations = [
            '\\UserFrosting\\Tests\\Unit\\Migrations\\Foo',
        ];

        $analyser = new MigrationDependencyAnalyser($migrations, []);

        $this->expectException(BadClassNameException::class);
        $analyser->analyse();
    }

    public function testAnalyserWithReordered()
    {
        $analyser = new MigrationDependencyAnalyser([
            '\\UserFrosting\\Tests\\Unit\\Migrations\\two\\CreateFlightsTable',
            'UserFrosting\\Tests\\Unit\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Unit\\Migrations\\one\\CreatePasswordResetsTable',
        ], []);

        $this->assertEquals([], $analyser->getUnfulfillable());
        $this->assertEquals([
            '\\UserFrosting\\Tests\\Unit\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Unit\\Migrations\\one\\CreatePasswordResetsTable',
            '\\UserFrosting\\Tests\\Unit\\Migrations\\two\\CreateFlightsTable',
        ], $analyser->getFulfillable());
    }

    public function testAnalyserWithUnfulfillable()
    {
        $migrations = [
            'UserFrosting\\Tests\\Unit\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Unit\\Migrations\\one\\CreatePasswordResetsTable',
            '\\UserFrosting\\Tests\\Unit\\Migrations\\UnfulfillableTable',
        ];

        $analyser = new MigrationDependencyAnalyser($migrations, []);

        $this->assertEquals([
            '\\UserFrosting\\Tests\\Unit\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Unit\\Migrations\\one\\CreatePasswordResetsTable',
        ], $analyser->getFulfillable());

        $this->assertEquals([
            '\\UserFrosting\\Tests\\Unit\\Migrations\\UnfulfillableTable' => '\UserFrosting\Tests\Integration\Migrations\NonExistingMigration',
        ], $analyser->getUnfulfillable());
    }*/
}
