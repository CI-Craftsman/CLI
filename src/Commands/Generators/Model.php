<?php
namespace Craftsman\Commands\Generators;

use Craftsman\Core\Generator;

/**
 * Generator\Model Command
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 */
class Model extends Generator implements \Craftsman\Interfaces\Command
{
    protected $name        = 'generate:model';
    protected $description = 'Generate a Model';
    protected $aliases     = ['g:model'];

    public function start()
    {
        $filename = ucfirst($this->getArgument('filename'));
				$appPath  = realpath(getenv('CI_APPPATH'));
        $appDir   = basename($appPath);

        $this->text(sprintf('Model path: <comment>./%s/models</comment>', $appDir));
        $this->text(sprintf('Filename: <comment>%s_model.php</comment>', $filename));

        // Confirm the action
        if ($this->confirm(sprintf('Do you want to create a %s Model?', $filename), true))
				{
            // We could try to create a directory if doesn't exist.
						$this->createDirectory(sprintf('%s/models', $appPath));

            $testFile = sprintf('%s/models/%s_model.php', $appPath, $filename);

            $options = array(
                'NAME'       => sprintf('%s_model', $filename),
                'COLLECTION' => $filename,
                'FILENAME'   => basename($testFile),
                'PATH'       => sprintf('./%s/models', $appDir)
            );

            if ($this->make($testFile, 'models/base.php.twig', $options))
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
