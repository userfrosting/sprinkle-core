<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Integration\Seeder;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Database\Seeder\BaseSeed;
use UserFrosting\Sprinkle\Core\Database\Seeder\Seeder;
use UserFrosting\Sprinkle\Core\Database\Seeder\SeedInterface;
use UserFrosting\Testing\ContainerStub;
use UserFrosting\UniformResourceLocator\ResourceLocator;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

// TODO : See notes in Seeder Class. This could be improved by mocking the class / locator (but locator also need to be removed here)

class SeederTests extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var Container $fakeCi
     */
    protected $fakeCi;

    /**
     * Setup our fake ci
     */
    /*public function setUp(): void
    {
        // Boot parent TestCase
        parent::setUp();

        // We must create our own CI with a custom locator for theses tests
        $this->fakeCi = ContainerStub::create();

        // Register services stub
        $locator = new ResourceLocator(__DIR__);
        $locator->registerStream('seeds', '', 'Seeds', true);
        $locator->registerLocation('Core', 'core/tests/Integration/');
        $this->fakeCi->set(ResourceLocatorInterface::class, $locator);
    }*/

    /**
     * @return Seeder
     */
    /*public function testSeeder(): Seeder
    {
        $seeder = $this->fakeCi->get(Seeder::class);
        $this->assertInstanceOf(Seeder::class, $seeder);

        return $seeder;
    }*/

    /**
     * @param Seeder $seeder
     * @depends testSeeder
     */
    /*public function testgetSeeds(Seeder $seeder)
    {
        $seeds = $seeder->getSeeds();
        $this->assertIsArray($seeds);
        $this->assertCount(3, $seeds);
        $this->assertEquals([
            [
                'name'     => 'Seed1',
                'class'    => '\\UserFrosting\\Sprinkle\\Core\\Database\\Seeds\\Seed1',
                'sprinkle' => 'Core',
            ],
            [
                'name'     => 'Seed2',
                'class'    => '\\UserFrosting\\Sprinkle\\Core\\Database\\Seeds\\Seed2',
                'sprinkle' => 'Core',
            ],
            [
                'name'     => 'Test/Seed',
                'class'    => '\\UserFrosting\\Sprinkle\\Core\\Database\\Seeds\\Test\\Seed',
                'sprinkle' => 'Core',
            ],
        ], $seeds);
    }*/

    /**
     * @param Seeder $seeder
     * @depends testSeeder
     */
    /*public function testGetSeed(Seeder $seeder)
    {
        $seed = $seeder->getSeed('Seed1');
        $this->assertIsArray($seed);
        $this->assertEquals([
            'name'     => 'Seed1',
            'class'    => '\\UserFrosting\\Sprinkle\\Core\\Database\\Seeds\\Seed1',
            'sprinkle' => 'Core',
        ], $seed);
    }*/

    /**
     * @param Seeder $seeder
     * @depends testSeeder
     */
    /*public function testUnfoundGetSeed(Seeder $seeder)
    {
        $this->expectException(\Exception::class);
        $seeder->getSeed('FakeSeed');
    }*/

    /**
     * @param Seeder $seeder
     * @depends testSeeder
     */
    /*public function testGetSeedClass(Seeder $seeder)
    {
        $seed = $seeder->getSeedClass('Seed1');
        $this->assertInstanceOf(SeedInterface::class, $seed);
    }*/

    /**
     * @param Seeder $seeder
     * @depends testSeeder
     */
    /*public function testGetSeedClassNotSeedInterface(Seeder $seeder)
    {
        $this->expectException(\Exception::class);
        $seeder->getSeedClass('Seed2'); // This class is not an instance of SeedInterface
    }*/

    /**
     * @param Seeder $seeder
     * @depends testSeeder
     */
    /*public function testGetSeedClassException(Seeder $seeder)
    {
        $this->expectException(\Exception::class);
        $seeder->getSeedClass('Test/Seed'); // The namespace in this class is wrong
    }*/

    /**
     * @param Seeder $seeder
     * @depends testSeeder
     */
    /*public function testExecuteSeed(Seeder $seeder)
    {
        // Get a fake seed
        $seed = m::mock(BaseSeed::class);
        $seed->shouldReceive('run');

        $seeder->executeSeed($seed);
    }*/
}
