<?php
namespace Craftsman\Commands\Generators;

use Craftsman\Core\Generator;

/**
 * Generator\Controller Command
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 */
class Controller extends Generator implements \Craftsman\Interfaces\Command
{
    protected $name        = 'generate:controller';
    protected $description = 'Generate a Controller';
    protected $aliases     = ['g:controller'];

    public function start()
    {
        $filename        = ucfirst($this->getArgument('filename'));
        $appPath         = realpath(getenv('APPPATH'));
        $appDir          = basename($appPath);
        $controllersPath = sprintf('%s/controllers', $appPath);
        $viewsPath       = sprintf('%s/views', $appPath);

        $this->text(sprintf(
          'Controller path: <comment>./%s/controllers</comment>',
          $appDir
        ));

        $this->text(sprintf('Filename: <comment>%s.php</comment>', $filename));

        // Confirm the action
        if ($this->confirm(sprintf('Do you want to create a %s Controller?', $filename), true))
        {
            $controllerFile = sprintf('%s/%s.php', $controllersPath, $filename);

            $this->createDirectory($controllersPath);

            $options = array(
                'NAME'       => $filename,
                'COLLECTION' => strtolower($filename),
                'FILENAME'   => basename($controllerFile),
                'PATH'       => sprintf('./%s/controllers', $appDir),
                'ACTIONS'    => $this->getArgument('options')
            );

            $this->comment('Controller');

            if ($this->make($controllerFile, 'controllers/base.php.twig', $options))
            {
                $this->text(sprintf('<info>create</info> %s/controllers/%s',
                  $appDir, basename($controllerFile)
                ));
            }

            $views = empty($options['ACTIONS'])
                ? array('index','get','create','edit')
                : $options['ACTIONS'];

            $resourcePath = sprintf('%s/%s', $viewsPath, strtolower($filename));

            $this->createDirectory($resourcePath);

            $options['EXT']      = '.php';
            $options['CLASS']    = $filename;
            $options['VIEWPATH'] = sprintf('./%s/views', $appDir);

            $this->comment('Views');

            foreach ($views as $view)
            {
                $viewFile = sprintf('%s/%s.php', $resourcePath, $view);
                $options['METHOD'] = $view;

                $this->make($viewFile, 'views/base.php.twig', $options);

                $this->text(sprintf('<info>create</info> %s/views/%s',
                    $appDir, basename($viewFile)
                ));
            }
        } else {
            $this->warning('Process aborted!');
        }
    }
}
