<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Database;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Core\Database\Models\Model;
// use UserFrosting\Support\Exception\BadRequestException;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;

/**
 * Test custom relations in `/src/Database/Relations`.
 */
class DatabaseTests extends TestCase
{
    /**
     * @var Builder
     */
    protected Builder $schema;

    /**
     * Setup the database schema.
     */
    public function setUp(): void
    {
        // Boot parent TestCase, which will set up the database and connections for us.
        parent::setUp();

        // Alias schema Builder
        $this->schema = $this->ci->get(Builder::class);

        // Create tests tables
        $this->createSchema();
    }

    /**
     * createSchema
     */
    protected function createSchema(): void
    {
        $this->schema->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
        });

        // Users have multiple email addresses
        $this->schema->create('emails', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('label');
            $table->string('email');
        });

        // Users have multiple phones (polymorphic - other entities can have phones as well)
        $this->schema->create('phones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('label');
            $table->string('number', 20);
            $table->morphs('phoneable');
        });

        // Users have multiple roles... (m:m)
        $this->schema->create('role_users', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('role_id')->unsigned();
        });

        $this->schema->create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug');
        });

        // And Roles have multiple permissions... (m:m)
        $this->schema->create('permission_roles', function (Blueprint $table) {
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned();
        });

        $this->schema->create('permissions', function ($table) {
            $table->increments('id');
            $table->string('slug');
        });

        // A user can be assigned to a specific task at a specific location
        $this->schema->create('tasks', function ($table) {
            $table->increments('id');
            $table->string('name');
        });

        $this->schema->create('locations', function ($table) {
            $table->increments('id');
            $table->string('name');
        });

        $this->schema->create('assignments', function ($table) {
            $table->integer('task_id')->unsigned();
            $table->integer('location_id')->unsigned();
            $table->morphs('assignable');
        });

        $this->schema->create('jobs', function ($table) {
            $table->integer('user_id')->unsigned();
            $table->integer('location_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->string('title');
        });
    }

    /**
     * Tear down the database schema.
     */
    public function tearDown(): void
    {
        $this->schema->drop('users');
        $this->schema->drop('emails');
        $this->schema->drop('phones');
        $this->schema->drop('role_users');
        $this->schema->drop('roles');
        $this->schema->drop('permission_roles');
        $this->schema->drop('permissions');
        $this->schema->drop('tasks');
        $this->schema->drop('locations');
        $this->schema->drop('assignments');
        $this->schema->drop('jobs');

        Relation::morphMap([], false);

        parent::tearDown();
    }

    public function testTableCreation(): void
    {
        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('emails'));
        $this->assertTrue($this->schema->hasTable('phones'));
        $this->assertTrue($this->schema->hasTable('role_users'));
        $this->assertTrue($this->schema->hasTable('roles'));
        $this->assertTrue($this->schema->hasTable('permission_roles'));
        $this->assertTrue($this->schema->hasTable('permissions'));
        $this->assertTrue($this->schema->hasTable('tasks'));
        $this->assertTrue($this->schema->hasTable('locations'));
        $this->assertTrue($this->schema->hasTable('assignments'));
        $this->assertTrue($this->schema->hasTable('jobs'));
    }

    /**
     * @depends testTableCreation
     * testOneToManyRelationship
     */
    public function testOneToManyRelationship(): void
    {
        $user = EloquentTestUser::create(['name' => 'David']);
        $user->emails()->create([
            'label' => 'primary',
            'email' => 'david@owlfancy.com',
        ]);
        $user->emails()->create([
            'label' => 'work',
            'email' => 'david@attenboroughsreef.com',
        ]);

        $emails = $user->emails;

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $emails);
        $this->assertEquals(2, $emails->count());
        $this->assertInstanceOf(EloquentTestEmail::class, $emails[0]);
        $this->assertInstanceOf(EloquentTestEmail::class, $emails[1]);
    }

    /**
     * @depends testTableCreation
     * Tests our custom HasManySyncable class.
     */
    public function testSyncOneToMany(): void
    {
        $user = EloquentTestUser::create(['name' => 'David']);

        // Set up original emails
        $user->emails()->create([
            'label' => 'primary',
            'email' => 'david@owlfancy.com',
        ]);
        $user->emails()->create([
            'label' => 'work',
            'email' => 'david@attenboroughsreef.com',
        ]);

        // Delete `work`, update `primary`, and add `gmail`
        $user->emails()->sync([
            [
                'id'    => 1,
                'email' => 'david@aol.com',
            ],
            [
                'label' => 'gmail',
                'email' => 'davidattenborough@gmail.com',
            ],
        ]);

        // Assert new state
        $this->assertEquals([
            [
                'id'      => 1,
                'user_id' => 1,
                'label'   => 'primary',
                'email'   => 'david@aol.com',
            ],
            [
                'id'      => 3,
                'user_id' => 1,
                'label'   => 'gmail',
                'email'   => 'davidattenborough@gmail.com',
            ],
        ], $user->emails->toArray());
    }

    /**
     * @depends testTableCreation
     * Tests our custom MorphManySyncable class.
     */
    public function testSyncMorphMany(): void
    {
        $user = EloquentTestUser::create(['name' => 'David']);

        // Set up original phones
        $user->phones()->create([
            'label'  => 'primary',
            'number' => '5555551212',
        ]);
        $user->phones()->create([
            'label'  => 'work',
            'number' => '2223334444',
        ]);

        // Delete `work`, update `primary`, and add `fax`
        $user->phones()->sync([
            [
                'id'     => 1,
                'number' => '8883332222',
            ],
            [
                'label'  => 'fax',
                'number' => '5550005555',
            ],
        ]);

        $phones = $user->phones->toArray();

        $this->assertEquals([
            [
                'id'             => 1,
                'phoneable_id'   => 1,
                'phoneable_type' => EloquentTestUser::class,
                'label'          => 'primary',
                'number'         => '8883332222',
            ],
            [
                'id'             => 3,
                'phoneable_id'   => 1,
                'phoneable_type' => EloquentTestUser::class,
                'label'          => 'fax',
                'number'         => '5550005555',
            ],
        ], $phones);
    }

    /**
     * @depends testTableCreation
     * testBelongsToManyUnique
     */
    public function testBelongsToManyUnique(): void
    {
        $user = EloquentTestUser::create(['name' => 'David']);

        $this->generateLocations();
        $this->generateRoles();
        $this->generateJobs();

        $expectedRoles = [
            [
                'id'    => 2,
                'slug'  => 'soldier',
                'pivot' => [
                    'user_id' => 1,
                    'role_id' => 2,
                ],
            ],
            [
                'id'    => 3,
                'slug'  => 'egg-layer',
                'pivot' => [
                    'user_id' => 1,
                    'role_id' => 3,
                ],
            ],
        ];

        $roles = $user->jobRoles;
        $this->assertEquals($expectedRoles, $roles->toArray());

        // Test eager loading
        $users = EloquentTestUser::with('jobRoles')->get();
        $this->assertEquals($expectedRoles, $users->toArray()[0]['job_roles']);
    }

    /**
     * testMorphsToManyUnique
     * @depends testTableCreation
     * @depends testBelongsToManyUnique
     */
    public function testMorphsToManyUnique(): void
    {
        $user = EloquentTestUser::create(['name' => 'David']);
        $user2 = EloquentTestUser::create(['name' => 'Alex']);

        $this->generateTasks();
        $this->generateLocations();
        $this->generateAssignments();

        $expectedTasks = [
            [
                'id'    => 2,
                'name'  => 'Chopping',
                'pivot' => [
                    'assignable_id'   => 1,
                    'task_id'         => 2,
                    'assignable_type' => EloquentTestUser::class,
                ],
            ],
            [
                'id'    => 3,
                'name'  => 'Baleing',
                'pivot' => [
                    'assignable_id'   => 1,
                    'task_id'         => 3,
                    'assignable_type' => EloquentTestUser::class,
                ],
            ],
        ];

        $tasks = $user->assignmentTasks;
        $this->assertEquals($expectedTasks, $tasks->toArray());

        // Test eager loading
        $users = EloquentTestUser::with('assignmentTasks')->get();
        $this->assertEquals($expectedTasks, $users->toArray()[0]['assignment_tasks']);
    }

    /**
     * testMorphsToManyUniqueWithTertiary
     * @depends testTableCreation
     * @depends testMorphsToManyUnique
     */
    public function testMorphsToManyUniqueWithTertiary(): void
    {
        $user = EloquentTestUser::create(['name' => 'David']);
        $user2 = EloquentTestUser::create(['name' => 'Alex']);

        $this->generateTasks();
        $this->generateLocations();
        $this->generateAssignments();

        $expectedTasks = [
            [
                'id'        => 2,
                'name'      => 'Chopping',
                'pivot'     => [
                    'assignable_id'   => 1,
                    'task_id'         => 2,
                    'assignable_type' => EloquentTestUser::class,
                ],
                'locations' => [
                    [
                        'id'    => 1,
                        'name'  => 'Hatchery',
                        'pivot' => [
                            'location_id'     => 1,
                            'task_id'         => 2,
                            'assignable_type' => EloquentTestUser::class,
                        ],
                    ],
                    [
                        'id'    => 2,
                        'name'  => 'Nexus',
                        'pivot' => [
                            'location_id'     => 2,
                            'task_id'         => 2,
                            'assignable_type' => EloquentTestUser::class,
                        ],
                    ],
                ],
            ],
            [
                'id'        => 3,
                'name'      => 'Baleing',
                'pivot'     => [
                    'assignable_id'   => 1,
                    'task_id'         => 3,
                    'assignable_type' => EloquentTestUser::class,
                ],
                'locations' => [
                    [
                        'id'    => 2,
                        'name'  => 'Nexus',
                        'pivot' => [
                            'location_id'     => 2,
                            'task_id'         => 3,
                            'assignable_type' => EloquentTestUser::class,
                        ],
                    ],
                ],
            ],
        ];

        $tasks = $user->tasks;
        $this->assertEquals($expectedTasks, $tasks->toArray());

        // Test eager loading
        $users = EloquentTestUser::with('tasks')->get();
        $this->assertEquals($expectedTasks, $users->toArray()[0]['tasks']);
    }

    /**
     * testBelongsToManyUniqueWithTertiary
     * @depends testTableCreation
     * @depends testBelongsToManyUnique
     */
    public function testBelongsToManyUniqueWithTertiary(): void
    {
        $user = EloquentTestUser::create(['name' => 'David']);

        $this->generateLocations();
        $this->generateRoles();
        $this->generateJobs();

        $expectedJobs = [
            [
                'id'        => 2,
                'slug'      => 'soldier',
                'pivot'     => [
                    'user_id' => 1,
                    'role_id' => 2,
                ],
                'locations' => [
                    [
                        'id'    => 1,
                        'name'  => 'Hatchery',
                        'pivot' => [
                            'title'       => 'Grunt',
                            'location_id' => 1,
                            'role_id'     => 2,
                        ],
                    ],
                    [
                        'id'    => 2,
                        'name'  => 'Nexus',
                        'pivot' => [
                            'title'       => 'Sergeant',
                            'location_id' => 2,
                            'role_id'     => 2,
                        ],
                    ],
                ],
            ],
            [
                'id'        => 3,
                'slug'      => 'egg-layer',
                'pivot'     => [
                    'user_id' => 1,
                    'role_id' => 3,
                ],
                'locations' => [
                    [
                        'id'    => 2,
                        'name'  => 'Nexus',
                        'pivot' => [
                            'title'       => 'Queen',
                            'location_id' => 2,
                            'role_id'     => 3,
                        ],
                    ],
                ],
            ],
        ];

        $jobs = $user->jobs()->withPivot('title')->get();
        $this->assertEquals($expectedJobs, $jobs->toArray());

        // Test eager loading
        $users = EloquentTestUser::with(['jobs' => function ($relation) {
            return $relation->withPivot('title');
        }])->get();

        $this->assertEquals($expectedJobs, $users->toArray()[0]['jobs']);
    }

    /**
     * testBelongsToManyUniqueWithTertiaryEagerLoad
     * @depends testTableCreation
     * @depends testBelongsToManyUniqueWithTertiary
     */
    public function testBelongsToManyUniqueWithTertiaryEagerLoad(): void
    {
        // Create test data
        EloquentTestUser::create(['name' => 'David']);
        EloquentTestUser::create(['name' => 'Alex']);
        $this->generateLocations();
        $this->generateRoles();
        $this->generateJobs();

        // Get both users from db
        $user1 = EloquentTestUser::with('jobs')->firstWhere(['name' => 'David']);
        $user2 = EloquentTestUser::with('jobs')->firstWhere(['name' => 'Alex']);

        // Assert both users individually
        $this->assertEquals([
            'id'   => 1,
            'name' => 'David',
            'jobs' => [
                [
                    'id'        => 2,
                    'slug'      => 'soldier',
                    'pivot'     => [
                        'user_id' => 1,
                        'role_id' => 2,
                    ],
                    'locations' => [
                        [
                            'id'    => 1,
                            'name'  => 'Hatchery',
                            'pivot' => [
                                'location_id' => 1,
                                'role_id'     => 2,
                            ],
                        ],
                        [
                            'id'    => 2,
                            'name'  => 'Nexus',
                            'pivot' => [
                                'location_id' => 2,
                                'role_id'     => 2,
                            ],
                        ],
                    ],
                ],
                [
                    'id'        => 3,
                    'slug'      => 'egg-layer',
                    'pivot'     => [
                        'user_id' => 1,
                        'role_id' => 3,
                    ],
                    'locations' => [
                        [
                            'id'    => 2,
                            'name'  => 'Nexus',
                            'pivot' => [
                                'location_id' => 2,
                                'role_id'     => 3,
                            ],
                        ],
                    ],
                ],
            ],
        ], $user1->toArray());

        $this->assertEquals([
            'id'   => 2,
            'name' => 'Alex',
            'jobs' => [
                [
                    'id'        => 3,
                    'slug'      => 'egg-layer',
                    'pivot'     => [
                        'user_id' => 2,
                        'role_id' => 3,
                    ],
                    'locations' => [
                        [
                            'id'    => 1,
                            'name'  => 'Hatchery',
                            'pivot' => [
                                'location_id' => 1,
                                'role_id'     => 3,
                            ],
                        ],
                    ],
                ],
            ],
        ], $user2->toArray());
    }

    /**
     * @depends testTableCreation
     * Test the ability of a BelongsToManyThrough relationship to retrieve structured data on a single model or set of models.
     */
    public function testBelongsToManyThrough(): void
    {
        $this->generateRolesWithPermissions();

        $user = EloquentTestUser::create(['name' => 'David']);

        $user->roles()->attach([1, 2]);

        // Test retrieval of via models as well
        // This user won't have `uri_royal_jelly` slug
        $this->assertEquals([
            [
                'id'    => 1,
                'slug'  => 'uri_harvest',
                'pivot' => [
                    'user_id'       => 1,
                    'permission_id' => 1,
                ],
            ],
            [
                'id'    => 2,
                'slug'  => 'uri_spit_acid',
                'pivot' => [
                    'user_id'       => 1,
                    'permission_id' => 2,
                ],
            ],
            [
                'id'    => 3,
                'slug'  => 'uri_slash',
                'pivot' => [
                    'user_id'       => 1,
                    'permission_id' => 3,
                ],
            ],
        ], $user->permissions->toArray());

        // Test counting
        $this->assertEquals(3, $user->permissions()->count());

        $user2 = EloquentTestUser::create(['name' => 'Alex']);
        $user2->roles()->attach([2, 3]);

        // Test eager load
        $users = EloquentTestUser::with('permissions')->get();
        $usersWithPermissions = $users->toArray();

        $this->assertEquals([
            [
                'id'    => 2,
                'slug'  => 'uri_spit_acid',
                'pivot' => [
                    'user_id'       => 2,
                    'permission_id' => 2,
                ],
            ],
            [
                'id'    => 3,
                'slug'  => 'uri_slash',
                'pivot' => [
                    'user_id'       => 2,
                    'permission_id' => 3,
                ],
            ],
            [
                'id'    => 4,
                'slug'  => 'uri_royal_jelly',
                'pivot' => [
                    'user_id'       => 2,
                    'permission_id' => 4,
                ],
            ],
        ], $usersWithPermissions[1]['permissions']);

        // Test counting related models (withCount)
        $users = EloquentTestUser::withCount('permissions')->get();

        // N.B.: Changed behavior in UF 4.6 due to change in Laravel API. Duplicated permission now count as two, instead of 1.
        // See : https://github.com/laravel/framework/issues/30575#issuecomment-554096259
        $this->assertEquals(4, $users[0]->permissions_count); // Used to be 3
        $this->assertEquals(4, $users[1]->permissions_count);

        $this->assertInstanceOf(EloquentTestPermission::class, $users[0]->permissions[0]);
        $this->assertInstanceOf(EloquentTestPermission::class, $users[0]->permissions[1]);
        $this->assertInstanceOf(EloquentTestPermission::class, $users[0]->permissions[2]);
    }

    /**
     * Test the ability of a BelongsToManyThrough relationship to retrieve and count paginated queries.
     * @depends testTableCreation
     * @depends testBelongsToManyThrough
     */
    public function testBelongsToManyThroughPaginated(): void
    {
        $this->generateRolesWithPermissions();

        $user = EloquentTestUser::create(['name' => 'David']);

        $user->roles()->attach([1, 2]);

        $paginatedPermissions = $user->permissions()->take(2)->offset(1);

        $this->assertEquals([
            [
                'id'    => 2,
                'slug'  => 'uri_spit_acid',
                'pivot' => [
                    'user_id'       => 1,
                    'permission_id' => 2,
                ],
            ],
            [
                'id'    => 3,
                'slug'  => 'uri_slash',
                'pivot' => [
                    'user_id'       => 1,
                    'permission_id' => 3,
                ],
            ],
        ], $paginatedPermissions->get()->toArray());

        $this->assertEquals(2, $paginatedPermissions->count());
    }

    /**
     * Test the ability of a BelongsToManyThrough relationship to retrieve and count paginated queries,
     * when we need to reference a virtual/computed column (for example in a sort).
     * @depends testTableCreation
     * @depends testBelongsToManyThrough
     */
    public function testBelongsToManyThroughPaginatedWithOrderByAggregateColumn(): void
    {
        $this->generateRolesWithPermissions();

        $user = EloquentTestUser::create(['name' => 'David']);
        $user->roles()->attach([1, 2]);

        // If the paginated query is being ordered correctly by including the `roles_count` computed column,
        // Then `uri_spit_acid` should appear first. If not, then the results will not be ordered and the `uri_harvest`
        // result will be returned, in accordance with the default database order.
        $paginatedPermissions = $user->permissions()->withCount('roles')->orderBy('roles_count', 'desc')->take(1)->offset(0);

        $this->assertEquals([
            [
                'id'          => 2,
                'slug'        => 'uri_spit_acid',
                'roles_count' => 3,
                'pivot'       => [
                    'user_id'       => 1,
                    'permission_id' => 2,
                ],
            ],
        ], $paginatedPermissions->get()->toArray());
    }

    /**
     * Test the ability of a BelongsToManyThrough relationship to retrieve structured data on a single model or set of models,
     * eager loading the "via" models at the same time.
     * @depends testTableCreation
     * @depends testBelongsToManyThrough
     */
    public function testBelongsToManyThroughWithVia(): void
    {
        $this->generateRolesWithPermissions();

        $user = EloquentTestUser::create(['name' => 'David']);

        $user->roles()->attach([1, 2]);

        // Test retrieval of via models as well
        $this->assertBelongsToManyThroughForDavid($user->permissions()->withVia('roles_via')->get()->toArray());

        $user2 = EloquentTestUser::create(['name' => 'Alex']);
        $user2->roles()->attach([2, 3]);

        // Test eager loading
        $users = EloquentTestUser::with(['permissions' => function ($query) {
            return $query->withVia('roles_via');
        }])->get();

        $this->assertInstanceOf(EloquentTestPermission::class, $users[0]->permissions[0]);
        $this->assertInstanceOf(EloquentTestRole::class, $users[0]->permissions[0]->roles_via[0]);

        $usersWithPermissions = $users->toArray();

        $this->assertBelongsToManyThroughForDavid($usersWithPermissions[0]['permissions']);
        $this->assertBelongsToManyThroughForAlex($usersWithPermissions[1]['permissions']);
    }

    /**
     * @depends testTableCreation
     * testQueryExclude
     */
    public function testQueryExclude(): void
    {
        $this->generateRoles();
        $this->generateJobs();
        $job = EloquentTestJob::exclude('location_id', 'title')->first();

        $this->assertEquals([
            'role_id' => 2,
            'user_id' => 1,
        ], $job->toArray());
    }

    /**
     * testQueryExcludeOnJoinedTable
     * @depends testTableCreation
     * @depends testQueryExclude
     */
    public function testQueryExcludeOnJoinedTable(): void
    {
        $this->generateRolesWithPermissions();

        $user = EloquentTestUser::create(['name' => 'David']);

        $user->roles()->attach([1, 2]);

        $users = EloquentTestUser::with(['permissions' => function ($query) {
            $query->exclude('slug');
        }])->get();

        $this->assertEquals([
            [
                'id'          => 1,
                'name'        => 'David',
                'permissions' => [
                    [
                        'id'    => 1,
                        'pivot' => [
                            'user_id'       => 1,
                            'permission_id' => 1,
                        ],
                    ],
                    [
                        'id'    => 2,
                        'pivot' => [
                            'user_id'       => 1,
                            'permission_id' => 2,
                        ],
                    ],
                    [
                        'id'    => 3,
                        'pivot' => [
                            'user_id'       => 1,
                            'permission_id' => 3,
                        ],
                    ],
                ],
            ],
        ], $users->toArray());
    }

    /**
     * testQueryExcludeUseQualifiedNamesOnJoinedTable
     * @depends testTableCreation
     * @depends testQueryExclude
     */
    public function testQueryExcludeUseQualifiedNamesOnJoinedTable(): void
    {
        $this->generateRolesWithPermissions();

        $user = EloquentTestUser::create(['name' => 'David']);

        $user->roles()->attach([1, 2]);

        $users = EloquentTestUser::with(['roles' => function ($query) {
            $query->addSelect('roles.*', 'jobs.*')
                    ->leftJoin('jobs', 'jobs.role_id', '=', 'roles.id')
                    ->exclude('slug', 'jobs.user_id', 'jobs.location_id', 'jobs.role_id')
                    ->orderBy('roles.id', 'asc');
        }])->get();

        $this->assertEquals([
            [
                'id'    => 1,
                'name'  => 'David',
                'roles' => [
                    [
                        'id'    => 1,
                        'title' => null,
                        'pivot' => [
                            'user_id' => 1,
                            'role_id' => 1,
                        ],
                    ],
                    [
                        'id'    => 2,
                        'title' => null,
                        'pivot' => [
                            'user_id' => 1,
                            'role_id' => 2,
                        ],
                    ],
                ],
            ],
        ], $users->toArray());
    }

    /**
     * testQueryExcludeWildcard
     * @depends testTableCreation
     * @depends testQueryExclude
     */
    public function testQueryExcludeWildcard(): void
    {
        $this->generateRoles();
        $this->generateJobs();
        $job = EloquentTestJob::select('*')->addSelect('user_id')->exclude('*')->first();

        $this->assertEquals([
            'user_id' => 1,
        ], $job->toArray());

        $job = EloquentTestJob::select('jobs.*')->addSelect('user_id')->exclude('*')->first();

        $this->assertEquals([
            'user_id' => 1,
        ], $job->toArray());

        $job = EloquentTestJob::select('*')->addSelect('user_id')->exclude('jobs.*')->first();

        $this->assertEquals([
            'user_id' => 1,
        ], $job->toArray());
    }

    /**
     * Helpers...
     */

    /**
     * generateRoles
     */
    protected function generateRoles(): array
    {
        return [
            EloquentTestRole::create([
                'id'   => 1,
                'slug' => 'forager',
            ]),

            EloquentTestRole::create([
                'id'   => 2,
                'slug' => 'soldier',
            ]),

            EloquentTestRole::create([
                'id'   => 3,
                'slug' => 'egg-layer',
            ]),
        ];
    }

    /**
     * generatePermissions
     */
    protected function generatePermissions(): array
    {
        return [
            EloquentTestPermission::create([
                'id'   => 1,
                'slug' => 'uri_harvest',
            ]),

            EloquentTestPermission::create([
                'id'   => 2,
                'slug' => 'uri_spit_acid',
            ]),

            EloquentTestPermission::create([
                'id'   => 3,
                'slug' => 'uri_slash',
            ]),

            EloquentTestPermission::create([
                'id'   => 4,
                'slug' => 'uri_royal_jelly',
            ]),
        ];
    }

    /**
     * generateRolesWithPermissions
     */
    protected function generateRolesWithPermissions(): array
    {
        $roles = $this->generateRoles();

        $this->generatePermissions();

        $roles[0]->permissions()->attach([1, 2]);
        // We purposefully want a permission that belongs to more than one role
        $roles[1]->permissions()->attach([2, 3]);
        $roles[2]->permissions()->attach([2, 4]);

        return $roles;
    }

    /**
     * generateJobs
     */
    protected function generateJobs(): array
    {
        /*
         * Sample data

        | user_id | role_id | location_id |
        |---------|---------|-------------|
        | 1       | 2       | 1           |
        | 1       | 2       | 2           |
        | 1       | 3       | 2           |
        | 2       | 3       | 1           |
        */

        return [
            EloquentTestJob::create([
                'role_id'     => 2,
                'location_id' => 1,
                'user_id'     => 1,
                'title'       => 'Grunt',
            ]),
            EloquentTestJob::create([
                'role_id'     => 2,
                'location_id' => 2,
                'user_id'     => 1,
                'title'       => 'Sergeant',
            ]),
            EloquentTestJob::create([
                'role_id'     => 3,
                'location_id' => 2,
                'user_id'     => 1,
                'title'       => 'Queen',
            ]),
            EloquentTestJob::create([
                'role_id'     => 3,
                'location_id' => 1,
                'user_id'     => 2,
                'title'       => 'Demi-queen',
            ]),
        ];
    }

    /**
     * generateLocations
     */
    protected function generateLocations(): array
    {
        return [
            EloquentTestLocation::create([
                'id'   => 1,
                'name' => 'Hatchery',
            ]),

            EloquentTestLocation::create([
                'id'   => 2,
                'name' => 'Nexus',
            ]),
        ];
    }

    /**
     * generateTasks
     */
    protected function generateTasks(): array
    {
        return [
            EloquentTestTask::create([
                'id'   => 1,
                'name' => 'Digging',
            ]),

            EloquentTestTask::create([
                'id'   => 2,
                'name' => 'Chopping',
            ]),

            EloquentTestTask::create([
                'id'   => 3,
                'name' => 'Baleing',
            ]),
        ];
    }

    /**
     * generateAssignments
     */
    protected function generateAssignments(): array
    {
        return [
            EloquentTestAssignment::create([
                'task_id'         => 2,
                'location_id'     => 1,
                'assignable_id'   => 1,
                'assignable_type' => EloquentTestUser::class,
            ]),
            EloquentTestAssignment::create([
                'task_id'         => 2,
                'location_id'     => 2,
                'assignable_id'   => 1,
                'assignable_type' => EloquentTestUser::class,
            ]),
            EloquentTestAssignment::create([
                'task_id'         => 3,
                'location_id'     => 2,
                'assignable_id'   => 1,
                'assignable_type' => EloquentTestUser::class,
            ]),
            EloquentTestAssignment::create([
                'task_id'         => 3,
                'location_id'     => 3,
                'assignable_id'   => 1,
                'assignable_type' => 'UserFrosting\Tests\Integration\EloquentTestNonExistant',
            ]),
            EloquentTestAssignment::create([
                'task_id'         => 3,
                'location_id'     => 1,
                'assignable_id'   => 2,
                'assignable_type' => EloquentTestUser::class,
            ]),
        ];
    }

    /**
     * assertBelongsToManyThroughForDavid
     * @param array $permissions
     */
    protected function assertBelongsToManyThroughForDavid(array $permissions)
    {
        // User should have effective permissions uri_harvest, uri_spit_acid, and uri_slash.
        // We also check that the 'roles_via' relationship is properly set.
        $this->assertEquals('uri_harvest', $permissions[0]['slug']);
        $this->assertEquals([
            [
                'id'    => 1,
                'slug'  => 'forager',
                'pivot' => [
                    'permission_id' => 1,
                    'role_id'       => 1,
                ],
            ],
        ], $permissions[0]['roles_via']);
        $this->assertEquals('uri_spit_acid', $permissions[1]['slug']);
        $this->assertEquals([
            [
                'id'    => 1,
                'slug'  => 'forager',
                'pivot' => [
                    'permission_id' => 2,
                    'role_id'       => 1,
                ],
            ],
            [
                'id'    => 2,
                'slug'  => 'soldier',
                'pivot' => [
                    'permission_id' => 2,
                    'role_id'       => 2,
                ],
            ],
        ], $permissions[1]['roles_via']);
        $this->assertEquals('uri_slash', $permissions[2]['slug']);
        $this->assertEquals([
            [
                'id'    => 2,
                'slug'  => 'soldier',
                'pivot' => [
                    'permission_id' => 3,
                    'role_id'       => 2,
                ],
            ],
        ], $permissions[2]['roles_via']);
    }

    /**
     * assertBelongsToManyThroughForAlex
     * @param array $permissions
     */
    protected function assertBelongsToManyThroughForAlex(array $permissions)
    {
        // User should have effective permissions uri_spit_acid, uri_slash, and uri_royal_jelly.
        // We also check that the 'roles_via' relationship is properly set.
        $this->assertEquals('uri_spit_acid', $permissions[0]['slug']);
        $this->assertEquals([
            [
                'id'    => 2,
                'slug'  => 'soldier',
                'pivot' => [
                    'permission_id' => 2,
                    'role_id'       => 2,
                ],
            ],
            [
                'id'    => 3,
                'slug'  => 'egg-layer',
                'pivot' => [
                    'permission_id' => 2,
                    'role_id'       => 3,
                ],
            ],
        ], $permissions[0]['roles_via']);
        $this->assertEquals('uri_slash', $permissions[1]['slug']);
        $this->assertEquals([
            [
                'id'    => 2,
                'slug'  => 'soldier',
                'pivot' => [
                    'permission_id' => 3,
                    'role_id'       => 2,
                ],
            ],
        ], $permissions[1]['roles_via']);
        $this->assertEquals('uri_royal_jelly', $permissions[2]['slug']);
        $this->assertEquals([
            [
                'id'    => 3,
                'slug'  => 'egg-layer',
                'pivot' => [
                    'permission_id' => 4,
                    'role_id'       => 3,
                ],
            ],
        ], $permissions[2]['roles_via']);
    }
}

/**
 * Eloquent Models...
 */
class EloquentTestModel extends Model
{
    /**
     * @var bool Disable timestamps for now.
     */
    public $timestamps = false;
}

class EloquentTestUser extends EloquentTestModel
{
    protected $table = 'users';
    protected $guarded = [];

    public function emails()
    {
        return $this->hasMany(EloquentTestEmail::class, 'user_id');
    }

    public function phones()
    {
        return $this->morphMany(EloquentTestPhone::class, 'phoneable');
    }

    /**
     * Get all roles to which this user belongs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(EloquentTestRole::class, 'role_users', 'user_id', 'role_id');
    }

    /**
     * Get all of the permissions this user has, via its roles.
     *
     * @return \UserFrosting\Sprinkle\Core\Database\Relations\BelongsToManyThrough
     */
    public function permissions()
    {
        return $this->belongsToManyThrough(
            EloquentTestPermission::class,
            EloquentTestRole::class,
            'role_users',
            'user_id',
            'role_id',
            'permission_roles',
            'role_id',
            'permission_id'
        );
    }

    /**
     * Get all of the user's unique tasks.
     */
    public function assignmentTasks()
    {
        $relation = $this->morphToManyUnique(
            EloquentTestTask::class,
            'assignable',
            'assignments',
            null,
            'task_id'   // Need to explicitly set this, since it doesn't match our related model name
        );

        return $relation;
    }

    /**
     * Get all of the user's unique tasks along with the task locations.
     */
    public function tasks()
    {
        $relation = $this->morphToManyUnique(
            EloquentTestTask::class,
            'assignable',
            'assignments',
            null,
            'task_id'   // Need to explicitly set this, since it doesn't match our related model name
        )->withTertiary(EloquentTestLocation::class, null, 'location_id');

        return $relation;
    }

    /**
     * Get all of the user's unique roles based on their jobs.
     */
    public function jobRoles()
    {
        $relation = $this->belongsToManyUnique(
            EloquentTestRole::class,
            'jobs',
            'user_id',
            'role_id'
        );

        return $relation;
    }

    /**
     * Get all of the user's unique roles based on their jobs as a tertiary relationship.
     */
    public function jobs()
    {
        $relation = $this->belongsToManyUnique(
            EloquentTestRole::class,
            'jobs',
            'user_id',
            'role_id'
        )->withTertiary(EloquentTestLocation::class, tertiaryKey: 'location_id');

        return $relation;
    }
}

class EloquentTestEmail extends EloquentTestModel
{
    protected $table = 'emails';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(EloquentTestUser::class, 'user_id');
    }
}

class EloquentTestPhone extends EloquentTestModel
{
    protected $table = 'phones';
    protected $guarded = [];

    public function phoneable()
    {
        return $this->morphTo();
    }
}

class EloquentTestRole extends EloquentTestModel
{
    protected $table = 'roles';
    protected $guarded = [];

    /**
     * Get a list of permissions assigned to this role.
     */
    public function permissions()
    {
        return $this->belongsToMany(EloquentTestPermission::class, 'permission_roles', 'role_id', 'permission_id');
    }
}

class EloquentTestPermission extends EloquentTestModel
{
    protected $table = 'permissions';
    protected $guarded = [];

    /**
     * Get a list of roles that have this permission.
     */
    public function roles()
    {
        return $this->belongsToMany(EloquentTestRole::class, 'permission_roles', 'permission_id', 'role_id');
    }
}

class EloquentTestTask extends EloquentTestModel
{
    protected $table = 'tasks';
    protected $guarded = [];

    public function locations()
    {
        return $this->belongsToMany(
            'UserFrosting\Tests\Integration\EloquentTestLocation',
            'assignments',
            'task_id',
            'location_id'
        );
    }
}

class EloquentTestLocation extends EloquentTestModel
{
    protected $table = 'locations';
    protected $guarded = [];
}

class EloquentTestAssignment extends EloquentTestModel
{
    protected $table = 'assignments';
    protected $guarded = [];
    protected $primaryKey = null;
    public $incrementing = false;

    /**
     * Get all of the users that are assigned to this assignment.
     */
    public function users()
    {
        return $this->morphedByMany(EloquentTestUser::class, 'assignable');
    }
}

class EloquentTestJob extends EloquentTestModel
{
    protected $table = 'jobs';
    protected $guarded = [];
    protected $primaryKey = null;
    public $incrementing = false;

    /**
     * Get the role for this job.
     */
    public function role()
    {
        return $this->belongsTo(EloquentTestRole::class, 'role_id');
    }
}
