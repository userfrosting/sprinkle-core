<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Sprunje;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Collection;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Slim\Psr7\Response;
use UserFrosting\Sprinkle\Core\Database\Migration;
use UserFrosting\Sprinkle\Core\Database\Models\Model as UfModel;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;
use UserFrosting\Sprinkle\Core\Sprunje\SprunjeException;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;

/**
 * Tests a basic Sprunje.
 */
class SprunjeTest extends CoreTestCase
{
    use MockeryPHPUnitIntegration;

    /** @var mixed[] */
    protected array $listable = [
        'name' => [
            // N.B.: Values are sorted automatically
            ['value' => 'bar', 'text' => 'bar'],
            ['value' => 'foo', 'text' => 'foo'],
            ['value' => 'foobar', 'text' => 'foobar'],
        ],
        'type' => [
            ['value' => 1, 'text' => 'TYPE A'],
            ['value' => 2, 'text' => 'TYPE B'],
        ],
    ];

    public function setUp(): void
    {
        parent::setUp();

        // Run custom migration up
        /** @var Builder */
        $builder = $this->ci->get(Builder::class);
        $migration = new TestTableMigration($builder);
        $migration->up();

        // Insert some data
        (new TestSprunjeModel([
            'id'          => 1,
            'name'        => 'foo',
            'description' => 'Le Foo',
            'type'        => 1,
            'active'      => true
        ]))->save();
        (new TestSprunjeModel([
            'id'          => 2,
            'name'        => 'bar',
            'description' => 'Le Bar',
            'type'        => 2,
            'active'      => false
        ]))->save();
        (new TestSprunjeModel([
            'id'          => 3,
            'name'        => 'foobar',
            'description' => 'Le Foo et le Bar',
            'type'        => 1,
            'active'      => true
        ]))->save();
    }

    public function tearDown(): void
    {
        // Run custom migration down
        /** @var Builder */
        $builder = $this->ci->get(Builder::class);
        $migration = new TestTableMigration($builder);
        $migration->down();

        parent::tearDown();
    }

    public function testBaseSprunje(): void
    {
        $sprunje = new TestSprunje();

        $this->assertEquals([
            'count'          => 3,
            'count_filtered' => 3,
            'rows'           => [
                ['id' => 1, 'name' => 'The foo', 'description' => 'Le Foo', 'type' => 1, 'active' => true],
                ['id' => 2, 'name' => 'The bar', 'description' => 'Le Bar', 'type' => 2, 'active' => false],
                ['id' => 3, 'name' => 'The foobar', 'description' => 'Le Foo et le Bar', 'type' => 1, 'active' => true],
            ],
            'listable' => $this->listable,
        ], $sprunje->getArray());
    }

    public function testWithPagination(): void
    {
        $sprunje = new TestSprunje([
            'size' => 1,
            'page' => 1, // First page is 0, so second row will be displayed.
        ]);

        $this->assertEquals([
            'count'          => 3,
            'count_filtered' => 3,
            'rows'           => [
                ['id' => 2, 'name' => 'The bar', 'description' => 'Le Bar', 'type' => 2, 'active' => false],
            ],
            'listable' => $this->listable,
        ], $sprunje->getArray());
    }

    public function testWithSort(): void
    {
        $sprunje = new TestSprunje();
        $sprunje->setOptions([
            'sorts' => ['id' => 'desc'],
        ]);

        $this->assertEquals([
            'count'          => 3,
            'count_filtered' => 3,
            'rows'           => [
                ['id' => 3, 'name' => 'The foobar', 'description' => 'Le Foo et le Bar', 'type' => 1, 'active' => true],
                ['id' => 2, 'name' => 'The bar', 'description' => 'Le Bar', 'type' => 2, 'active' => false],
                ['id' => 1, 'name' => 'The foo', 'description' => 'Le Foo', 'type' => 1, 'active' => true],
            ],
            'listable' => $this->listable,
        ], $sprunje->getArray());
    }

    /**
     * Name sort is custom (forced not sort for test).
     */
    public function testWithCustomSort(): void
    {
        $sprunje = new TestSprunje([
            'sorts' => ['name' => 'desc'],
        ]);

        $this->assertEquals([
            'count'          => 3,
            'count_filtered' => 3,
            'rows'           => [
                ['id' => 1, 'name' => 'The foo', 'description' => 'Le Foo', 'type' => 1, 'active' => true],
                ['id' => 2, 'name' => 'The bar', 'description' => 'Le Bar', 'type' => 2, 'active' => false],
                ['id' => 3, 'name' => 'The foobar', 'description' => 'Le Foo et le Bar', 'type' => 1, 'active' => true],
            ],
            'listable' => $this->listable,
        ], $sprunje->getArray());
    }

    public function testWithSortException(): void
    {
        $sprunje = new TestSprunje([
            'sorts' => ['description' => 'desc'],
        ]);

        $this->expectException(SprunjeException::class);
        $this->expectExceptionMessage('Bad sort: description');
        $sprunje->getArray();
    }

    public function testWithFilter(): void
    {
        $sprunje = new TestSprunje([
            'filters' => ['type' => 1],
        ]);

        $this->assertEquals([
            'count'          => 3,
            'count_filtered' => 2,
            'rows'           => [
                ['id' => 1, 'name' => 'The foo', 'description' => 'Le Foo', 'type' => 1, 'active' => true],
                ['id' => 3, 'name' => 'The foobar', 'description' => 'Le Foo et le Bar', 'type' => 1, 'active' => true],
            ],
            'listable' => $this->listable,
        ], $sprunje->getArray());
    }

    public function testWithEmptyFilter(): void
    {
        $sprunje = new TestSprunje([
            'filters' => ['type' => 3],
        ]);

        $this->assertEquals([
            'count'          => 3,
            'count_filtered' => 0,
            'rows'           => [],
            'listable'       => $this->listable,
        ], $sprunje->getArray());
    }

    /**
     * Name filter is custom (forced no filter for test).
     */
    public function testWithCustomFilter(): void
    {
        $sprunje = new TestSprunje([
            'filters' => ['name' => 'foo'],
        ]);

        $this->assertEquals([
            'count'          => 3,
            'count_filtered' => 3,
            'rows'           => [
                ['id' => 1, 'name' => 'The foo', 'description' => 'Le Foo', 'type' => 1, 'active' => true],
                ['id' => 2, 'name' => 'The bar', 'description' => 'Le Bar', 'type' => 2, 'active' => false],
                ['id' => 3, 'name' => 'The foobar', 'description' => 'Le Foo et le Bar', 'type' => 1, 'active' => true],
            ],
            'listable' => $this->listable,
        ], $sprunje->getArray());
    }

    /**
     * Name is forced to not be filterable by custom method...
     * So all will only actually work with "description" & "type" columns.
     */
    public function testWithAllFilter(): void
    {
        $sprunje = new TestSprunje([
            'filters' => ['_all' => 'Bar'],
        ]);

        $this->assertEquals([
            'count'          => 3,
            'count_filtered' => 2,
            'rows'           => [
                ['id' => 2, 'name' => 'The bar', 'description' => 'Le Bar', 'type' => 2, 'active' => false],
                ['id' => 3, 'name' => 'The foobar', 'description' => 'Le Foo et le Bar', 'type' => 1, 'active' => true],
            ],
            'listable' => $this->listable,
        ], $sprunje->getArray());
    }

    public function testWithTrueBooleanFilter(): void
    {
        $sprunje = new TestSprunje();
        $sprunje->setOptions([
            'filters' => ['active' => true],
        ]);

        $this->assertEquals([
            'count'          => 3,
            'count_filtered' => 2,
            'rows'           => [
                ['id' => 1, 'name' => 'The foo', 'description' => 'Le Foo', 'type' => 1, 'active' => true],
                // ['id' => 2, 'name' => 'The bar', 'description' => 'Le Bar', 'type' => 2, 'active' => false],
                ['id' => 3, 'name' => 'The foobar', 'description' => 'Le Foo et le Bar', 'type' => 1, 'active' => true],
            ],
            'listable' => $this->listable,
        ], $sprunje->getArray());
    }

    public function testWithFalseBooleanFilter(): void
    {
        $sprunje = new TestSprunje();
        $sprunje->setOptions([
            'filters' => ['active' => false],
        ]);

        $this->assertEquals([
            'count'          => 3,
            'count_filtered' => 1,
            'rows'           => [
                // ['id' => 1, 'name' => 'The foo', 'description' => 'Le Foo', 'type' => 1, 'active' => true],
                ['id' => 2, 'name' => 'The bar', 'description' => 'Le Bar', 'type' => 2, 'active' => false],
                // ['id' => 3, 'name' => 'The foobar', 'description' => 'Le Foo et le Bar', 'type' => 1, 'active' => true],
            ],
            'listable' => $this->listable,
        ], $sprunje->getArray());
    }

    public function testWithFilterException(): void
    {
        $sprunje = new TestSprunje([
            'filters' => ['id' => '2'],
        ]);

        $this->expectException(SprunjeException::class);
        $this->expectExceptionMessage('Bad filter: id');
        $sprunje->getArray();
    }

    public function testCSV(): void
    {
        $sprunje = new TestSprunje([
            'format' => 'csv',
        ]);
        $response = $sprunje->toResponse(new Response());
        $csv = (string) $response->getBody();

        $this->assertEquals('text/csv; charset=utf-8', $response->getHeaderLine('Content-Type'));
        $this->assertEquals('attachment;filename=export.csv', $response->getHeaderLine('Content-Disposition'));

        // Str_replace is required to normalize line endings.
        // See: https://stackoverflow.com/a/3986325/445757
        $this->assertSame(
            'id,name,description,type,active\n' .
            '1,"The foo","Le Foo",1,1\n' .
            '2,"The bar","Le Bar",2,\n' .
            '3,"The foobar","Le Foo et le Bar",1,1\n',
            str_replace("\n", '\n', $csv)
        );
    }

    public function testCSVWithArrayItem(): void
    {
        $sprunje = new ArrayTestSprunje([
            'format' => 'csv',
        ]);
        $response = $sprunje->toResponse(new Response());
        $csv = (string) $response->getBody();

        $this->assertSame(
            'id,name,description,type,active\n' .
            '1,,"Le Foo",1,1\n' .
            '2,,"Le Bar",2,\n' .
            '3,,"Le Foo et le Bar",1,1\n',
            str_replace("\n", '\n', $csv)
        );
    }

    public function testToResponseWithJson(): void
    {
        $sprunje = new TestSprunje();
        $response = $sprunje->toResponse(new Response());

        $this->assertJsonResponse([
            'count'          => 3,
            'count_filtered' => 3,
            'rows'           => [
                ['id' => 1, 'name' => 'The foo', 'description' => 'Le Foo', 'type' => 1, 'active' => true],
                ['id' => 2, 'name' => 'The bar', 'description' => 'Le Bar', 'type' => 2, 'active' => false],
                ['id' => 3, 'name' => 'The foobar', 'description' => 'Le Foo et le Bar', 'type' => 1, 'active' => true],
            ],
            'listable' => $this->listable,
        ], $response);
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }
}

class TestSprunje extends Sprunje
{
    protected array $filterable = [
        'name',
        'description',
        'type',
        'active',
    ];

    protected array $sortable = [
        'id',
        'name',
    ];

    protected array $listable = [
        'name',
        'type',
    ];

    protected function baseQuery(): EloquentBuilder|QueryBuilder|Relation|Model
    {
        return new TestSprunjeModel();
    }

    protected function applyTransformations(Collection $collection): Collection
    {
        $collection = $collection->map(function ($item, $key) {
            $item['name'] = 'The ' . $item['name'];

            return $item;
        });

        return $collection;
    }

    protected function sortName(EloquentBuilder|QueryBuilder|Relation $query, string $direction): static
    {
        return $this;
    }

    protected function filterName(EloquentBuilder|QueryBuilder|Relation $query, string $value): static
    {
        return $this;
    }

    /** @return array{value: int, text: string}[] */
    protected function listType(): array
    {
        return [
            [
                'value' => 1,
                'text'  => 'TYPE A',
            ],
            [
                'value' => 2,
                'text'  => 'TYPE B',
            ],
        ];
    }
}

class ArrayTestSprunje extends TestSprunje
{
    protected function applyTransformations(Collection $collection): Collection
    {
        $collection = $collection->map(function ($item, $key) {
            $item['name'] = [];

            return $item;
        });

        return $collection;
    }
}

class TestSprunjeModel extends UfModel
{
    protected $table = 'test_sprunje';

    protected $fillable = [
        'id',
        'name',
        'description',
        'type',
        'active',
    ];

    /** @var bool */
    public $timestamps = false;

    // @phpstan-ignore-next-line
    protected $casts = [
        'type'   => 'integer',
        'active' => 'boolean',
    ];
}

/**
 * Custom migration for testing.
 */
class TestTableMigration extends Migration
{
    public function up(): void
    {
        $this->schema->create('test_sprunje', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description');
            $table->integer('type');
            $table->boolean('active');
        });
    }

    public function down(): void
    {
        $this->schema->drop('test_sprunje');
    }
}
