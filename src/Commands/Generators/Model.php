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

	public function start()
	{
        $filename = ucfirst($this->getArgument('filename'));
        $basepath = rtrim($this->getOption('path'),'/').'/models/';

		$this->text('Controller path: <comment>'.$basepath.'</comment>');
		$this->text('Filename: <comment>'.$filename.'_model.php</comment>');

        // Confirm the action
	    if($this->confirm('Do you want to create a '.$filename.' Model?', TRUE))
	    {
			// We could try to create a directory if doesn't exist.
			(! $this->_filesystem->exists($basepath)) && $this->_filesystem->mkdir($basepath);

	    	$test_file = $basepath.$filename.'_model.php';
				
	    	$options = array(
	    		'NAME' => $filename.'_model',
	    		'COLLECTION' => $filename,
	    		'FILENAME'   => basename($test_file),
	    		'PATH'       => $test_file
	    	);
	    	if ($this->make($test_file, 'models/base.php.twig', $options))
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
