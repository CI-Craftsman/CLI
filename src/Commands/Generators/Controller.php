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
	protected $aliases 		 = ['g:controller'];

	public function start()
	{
    $filename = ucfirst($this->getArgument('filename'));
		$basepath = rtrim(preg_replace('/controllers/', '', $this->getOption('path')),'/');
		$appdir   = basename($basepath);

		$controllersPath = $basepath.'/controllers';
		$viewsPath = $basepath.'/views';

		$this->text("Controller path: <comment>{$appdir}/controllers</comment>");
		$this->text("Filename: <comment>{$filename}.php</comment>");

    // Confirm the action
	  if($this->confirm("Do you want to create a {$filename} Controller?", TRUE))
	  {
			$controllerFile = "{$controllersPath}/{$filename}.php";

			// We could try to create a directory if doesn't exist.
			(! $this->_filesystem->exists($controllersPath)) && $this->_filesystem->mkdir($controllersPath);

	    $options = array(
	    	'NAME'       => $filename,
	    	'COLLECTION' => strtolower($filename),
	    	'FILENAME'   => basename($controllerFile),
	    	'PATH'       => "./{$appdir}/controllers",
	    	'ACTIONS'    => $this->getArgument('options')
	    );

	    $this->comment('Controller');

	    if ($this->make($controllerFile, 'controllers/base.php.twig', $options))
	    {
	    	$this->text("<info>create</info> {$appdir}/controllers/".basename($controllerFile));
	    }

	    $views = empty($options['ACTIONS'])
	    	? array('index','get','create','edit')
	    	: $options['ACTIONS'];

	    $resourcePath = "{$viewsPath}/".strtolower($filename);

	   	// We could try to create a directory if doesn't exist.
			(! $this->_filesystem->exists($resourcePath)) && $this->_filesystem->mkdir($resourcePath);

	    $options['EXT']      = '.php';
	    $options['CLASS']    = $filename;
	    $options['VIEWPATH'] = "{$appdir}/views";

	    $this->comment('Views');

	    foreach ($views as $view)
	    {
	    	$viewFile = "{$resourcePath}/{$view}.php";
	    	$options['METHOD'] = $view;

	    	$this->make($viewFile, 'views/base.php.twig',$options);
	    	$this->text("<info>create</info> {$appdir}/views/".basename($viewFile));
	    }
	  }
	  else
	  {
	    $this->warning('Process aborted!');
	  }
	}
}
