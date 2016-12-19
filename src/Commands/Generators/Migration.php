<?php
namespace Craftsman\Commands\Generators;

use Craftsman\Core\Generator;
use Symfony\Component\Console\Input\InputOption;

/**
* Generator\Migration Command
*
* @package     Craftsman
* @author      David Sosa Valdes
* @link        https://github.com/davidsosavaldes/Craftsman
* @copyright   Copyright (c) 2016, David Sosa Valdes.
*/
class Migration extends Generator implements \Craftsman\Interfaces\Command
{
	protected $name        = 'generate:migration';
	protected $description = 'Generate a Migration';
	protected $aliases 		 = ['g:migration'];

	public function configure()
	{
		parent::configure();

		$this
			->addOption(
				'sequential',
				NULL,
				InputOption::VALUE_NONE,
				'If set, the migration will run with sequential mode active'
			);
	}

	public function start()
	{
		// Set default timezone if we use a timestamp migration version.
		date_default_timezone_set('UTC');

		$migration_regex = ($this->getOption('sequential') !== FALSE)
			? '/^\d{3}_(\w+)$/'
			: '/^\d{14}_(\w+)$/';

		$filename   = $this->getArgument('filename');
		$basepath   = rtrim(preg_replace(['/migrations/','/migration/'], ['',''], $this->getOption('path')),'/');
		$appdir 		= basename($basepath);
		$migrations = array();

		if ($this->_filesystem->exists($basepath.'/migrations'))
		{
			// And now let's figure out the migration target version
			if ($handle = opendir($basepath.'/migrations'))
			{
				while (($entry = readdir($handle)) !== FALSE)
				{
					if ($entry == "." && $entry == "..")
					{
						continue;
					}
					if (preg_match($migration_regex, $file = basename($entry, '.php')))
					{
						$number = sscanf($file, '%[0-9]+', $number)? $number : '0';
						if (isset($migrations[$number]))
						{
							throw new \RuntimeException("Cannot be duplicate migration numbers");
						}
						$migrations[$number] = $file;
					}
				}
				closedir($handle);
				ksort($migrations);
			}
		}

		$versions = array_keys($migrations);
		end($versions);

		$target_version = ($this->getOption('sequential') !== FALSE)
			? sprintf('%03d', abs(end($versions)) + 1)
			: date("YmdHis");

		// Maybe something wrong with the target version?
		if ($target_version <= current($versions))
		{
			$this->note("There's something wrong with the target version, we need to replace it with a new one.");
			$target_version = abs(current($versions)) + 1;
		}

		$target_file = $target_version."_".$filename.".php";

		// $this->text('(In '.$basepath.')');
		$this->newLine();
		$this->text('Migration path: <comment>'.basename($basepath).'/migrations/</comment>');
		$this->text('Filename: <comment>'.$target_file.'</comment>');

		// Confirm the action
		if($this->confirm('Do you want to create a '.$filename.' Migration?', TRUE))
		{
			// We could try to create a directory if doesn't exist.
			(! $this->_filesystem->exists($basepath.'/migrations')) && $this->_filesystem->mkdir($basepath.'/migrations');

			$test_file = $basepath.'/migrations/'.$target_file;
	    // Set the migration template arguments
	    list($_type) = explode('_', $this->getArgument('filename'));

	    $options = array(
	      'NAME'    	 => ucfirst($this->getArgument('filename')),
	      'FILENAME' 	 => $target_file,
	      'PATH'       => "./{$appdir}/migrations",
	      'TABLE_NAME' => str_replace($_type.'_', '', $this->getArgument('filename')),
	      'FIELDS'     => (array) $this->getArgument('options')
	    );

	    switch ($_type)
	    {
	   		case 'add':
	   		case 'create':
	   		case 'new':
	       	$template = 'migrations/create.php.twig';
	       	break;
	   		case 'update':
	   		case 'modify':
	       	$template = 'migrations/modify.php.twig';
	       	empty($options['FIELDS'])
						&& $options['FIELDS'] = array('column_name:column_type');
	       	break;
	   		default:
	       	$template = 'migrations/default.php.twig';
	       	break;
	    }

			if ($this->make($test_file, $template, $options))
			{
				$this->success('Migration created successfully!');
			}
		}
		else
		{
			$this->warning('Process aborted!');
		}
	}
}
