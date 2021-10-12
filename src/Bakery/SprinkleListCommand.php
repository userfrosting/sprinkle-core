<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Bakery\WithSymfonyStyle;
use UserFrosting\Sprinkle\SprinkleManager;

/**
 * Sprinkle:list CLI tool.
 */
class SprinkleListCommand extends Command
{
    use WithSymfonyStyle;

    /**
     * @var array The table header
     */
    protected $headers = ['Sprinkle', 'Class', 'Path'];

    /** @Inject */
    protected SprinkleManager $sprinkleManager;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sprinkle:list')
             ->setDescription('List all available sprinkles and their params');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Loaded Sprinkles');

        // Get sprinkle list
        $sprinkles = $this->sprinkleManager->getSprinkles();

        // Compile the routes into a displayable format
        $sprinklesTable = collect($sprinkles)->map(function ($sprinkle) {
            return [
                'sprinkle'  => $sprinkle::getName(),
                'class'     => $sprinkle,
                'path'      => $sprinkle::getPath(),
            ];
        })->all();

        // Display table
        $this->io->table($this->headers, $sprinklesTable);

        return self::SUCCESS;
    }
}
