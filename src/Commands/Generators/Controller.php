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

	public function start()
	{
    $filename = ucfirst($this->getArgument('filename'));
    // $basepath = rtrim($this->getOption('path'),'/').'/controllers/';
		$basepath = rtrim(preg_replace('/controllers/', '', $this->getOption('path')),'/');
		$basepath.= '/controllers';

		$this->text("Controller path: <comment>{$basepath}</comment>");
		$this->text("Filename: <comment>{$filename}.php</comment>");

    // Confirm the action
	  if($this->confirm("Do you want to create a {$filename} Controller?", TRUE))
	  {
			$test_file = "{$basepath}/{$filename}.php";

			// We could try to create a directory if doesn't exist.
			(! $this->_filesystem->exists($basepath)) && $this->_filesystem->mkdir($basepath);

	    $options = array(
	    	'NAME'       => $filename,
	    	'COLLECTION' => strtolower($filename),
	    	'FILENAME'   => basename($test_file),
	    	'PATH'       => $test_file,
	    	'ACTIONS'    => $this->getArgument('options')
	    );

	    $this->comment('Controller');

	    if ($this->make($test_file, 'controllers/base.php.twig', $options))
	    {
	    	$this->text("<info>create</info> {$test_file}");
	    }

	    $views = empty($options['ACTIONS'])
	    	? array('index','get','create','edit')
	    	: $options['ACTIONS'];

	    $viewpath = rtrim($this->getOption('path'),'/').'/views/'.strtolower($filename);

	   	// We could try to create a directory if doesn't exist.
			(! $this->_filesystem->exists($viewpath)) && $this->_filesystem->mkdir($viewpath);

	    $options['EXT']      = '.php';
	    $options['CLASS']    = $filename;
	    $options['VIEWPATH'] = $viewpath;

	    $this->comment('Views');

	    foreach ($views as $view)
	    {
	    	$viewfile = "{$viewpath}/{$view}.php";
	    	$options['METHOD'] = $view;

	    	$this->make($viewfile, 'views/base.php.twig',$options);
	    	$this->text("<info>create</info> {$viewfile}");
	    }
	  }
	  else
	  {
	    $this->warning('Process aborted!');
	  }
	}
}
