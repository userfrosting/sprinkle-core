<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Filesystem;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager as LaravelFilesystemManager;
use League\Flysystem\FilesystemInterface;
use UserFrosting\Support\Repository\Repository as Config;

/**
 * Filesystem disk manager service.
 */
class FilesystemManager extends LaravelFilesystemManager
{
    /**
     * Create a new filesystem manager instance.
     *
     * @param Config $config
     */
    public function __construct(protected Config $config)
    {
    }

    /**
     * Call a custom driver creator.
     *
     * @param array $config
     *
     * @return Filesystem
     */
    protected function callCustomCreator(array $config): Filesystem
    {
        $driver = $this->customCreators[$config['driver']]($this->config, $config);

        if ($driver instanceof FilesystemInterface) {
            return $this->adapt($driver);
        }

        return $driver;
    }

    /**
     * Get the filesystem connection configuration.
     *
     * @param string $name
     *
     * @return array
     */
    protected function getConfig($name): array
    {
        return $this->config->get("filesystems.disks.{$name}");
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('filesystems.default');
    }

    /**
     * Get the default cloud driver name.
     *
     * @return string
     */
    public function getDefaultCloudDriver(): string
    {
        return $this->config->get('filesystems.cloud');
    }
}
