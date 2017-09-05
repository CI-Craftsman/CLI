<?php
namespace Craftsman\Commands\Generators;

use Craftsman\Core\Generator;

/**
 * Generator\Seeder Command
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 */
class Seeder extends Generator implements \Craftsman\Interfaces\Command
{
    protected $name        = 'generate:seeder';
    protected $description = 'Generate a Seeder';
    protected $aliases     = ['g:seeder'];

    public function start()
    {
        $filename = ucfirst($this->getArgument('filename'));
        $appPath  = realpath(getenv('APPPATH'));
        $appDir   = basename($appPath);

        $this->text(sprintf('Seeder path: <comment>./%s/seeders</comment>', $appDir));
        $this->text(sprintf('Filename: <comment>%s.php</comment>', $filename));

        // Confirm the action
        if ($this->confirm(sprintf('Do you want to create a %s Seeder?', $filename), true))
        {
            // We could try to create a directory if doesn't exist.
            $this->createDirectory(sprintf('%s/seeders', $appPath));

            $testFile = sprintf('%s/seeders/%s.php', $appPath, $filename);

            $options = array(
                'NAME'       => $filename,
                'COLLECTION' => $filename,
                'FILENAME'   => basename($testFile),
                'PATH'       => sprintf('./%s/seeders', $appDir)
            );

            if ($this->make($testFile, 'seeders/base.php.twig', $options))
            {
                $this->success('Model created successfully!');
            }
        }
        else
        {
            $this->warning('Process aborted!');
        }
    }
}
