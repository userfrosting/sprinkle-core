<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery;

use Illuminate\Cache\Repository as Cache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Bakery\WithSymfonyStyle;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Twig\CacheHelper;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * ClearCache CLI Command.
 */
class ClearCacheCommand extends Command
{
    use WithSymfonyStyle;

    /** @Inject */
    protected Cache $cache;

    /** @Inject */
    protected Config $config;

    /** @Inject */
    protected ResourceLocatorInterface $locator;

    /** @Inject */
    protected CacheHelper $cacheHelper;

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('clear-cache')
             ->setDescription('Clears the application cache. Includes cache service, Twig and Router cached data');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Clearing cache');

        // Clear normal cache
        $this->io->writeln('<info> > Clearing Cache Instance</info>', OutputInterface::VERBOSITY_VERBOSE);
        $this->clearIlluminateCache();

        // Clear Twig cache
        $this->io->writeln('<info> > Clearing Twig Cached Data</info>', OutputInterface::VERBOSITY_VERBOSE);
        if (!$this->clearTwigCache()) {
            $this->io->error('Failed to clear Twig cached data. Make sure you have write access to the `app/cache/twig` directory.');

            return self::FAILURE;
        }

        // Clear router cache
        // TODO : Requires rewrite of RouteServices
        /*$this->io->writeln('<info> > Clearing Router cache file</info>', OutputInterface::VERBOSITY_VERBOSE);
        if (!$this->clearRouterCache()) {
            $filename = $this->config->get('settings.routerCacheFile');
            $file = $this->locator->getResource("cache://$filename", true);
            $this->io->error("Failed to delete Router cache file. Make sure you have write access to the `$file` file.");
            exit(1);
        }*/

        $this->io->success('Cache cleared !');

        return self::SUCCESS;
    }

    /**
     * Flush the cached data from the cache service.
     */
    protected function clearIlluminateCache(): void
    {
        // @phpstan-ignore-next-line - False positive. PHPStan doesn't use the right class for cache.
        $this->cache->flush();
    }

    /**
     * Clear the Twig cache using the Twig CacheHelper class.
     *
     * @return bool true/false if operation is successful
     */
    protected function clearTwigCache(): bool
    {
        return $this->cacheHelper->clearCache();
    }

    /**
     * Clear the Router cache data file.
     *
     * @return bool true/false if operation is successful
     */
    // TODO : Requires rewrite of RouteServices
    // protected function clearRouterCache()
    // {
    //     return $this->ci->router->clearCache();
    // }
}
