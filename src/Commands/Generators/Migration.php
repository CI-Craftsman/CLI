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
    protected $aliases     = ['g:migration'];

    public function configure()
    {
        parent::configure();

        $this
            ->addOption(
                'sequential',
                null,
                InputOption::VALUE_NONE,
                'If set, the migration will run with sequential mode active'
            )
						->addOption(
							'timezone',
							null,
							InputOption::VALUE_REQUIRED,
							'Set default timezone if we use a timestamp migration version.',
							'UTC'
						);
    }

    public function start()
    {
        // Set default timezone if we use a timestamp migration version.
        date_default_timezone_set($this->getOption('timezone'));

        $filename   		= $this->getArgument('filename');
				$appPath    		= realpath(getenv('CI_APPPATH'));
        $appDir     		= basename($appPath);
        $migrationsPath = sprintf('%s/migrations', $appPath);
        $migrations 		= array();

				$migrationRegex = ($this->getOption('sequential') !== false)
						? '/^\d{3}_(\w+)$/'
						: '/^\d{14}_(\w+)$/';

        if ($this->fs->exists($migrationsPath))
				{
            // And now let's figure out the migration target version
            if ($handle = opendir($migrationsPath))
						{
                while (($entry = readdir($handle)) !== false)
								{
                    if ($entry == "." && $entry == "..") { continue; }

                    if (preg_match($migrationRegex, $file = basename($entry, '.php')))
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

        $targetVersion = ($this->getOption('sequential') !== false)
            ? sprintf('%03d', abs(end($versions)) + 1)
            : date("YmdHis");

        // Maybe something wrong with the target version?
        if ($targetVersion <= current($versions))
				{
            $this->note("There's something wrong with the target version, we need to replace it with a new one.");
            $targetVersion = abs(current($versions)) + 1;
        }

        $targetFile = sprintf('%s_%s.php', $targetVersion, $filename);

        $this->text(sprintf('(In: %s)', getcwd()));
        $this->newLine();
        $this->text(sprintf('Migration path: <comment>./%s/migrations/</comment>', $appDir));
        $this->text(sprintf('Filename: <comment>%s</comment>', $targetFile));

        // Confirm the action
        if ($this->confirm(sprintf('Do you want to create a %s Migration?', $filename), true))
				{
            // We could try to create a directory if doesn't exist.
						$this->createDirectory(sprintf('%s/migrations', $appPath));

            $testFile = sprintf('%s/migrations/%s', $appPath, $targetFile);
            // Set the migration template arguments
            list($_type) = explode('_', $this->getArgument('filename'));

            $options = array(
                'NAME'       => ucfirst($this->getArgument('filename')),
                'FILENAME'   => $targetFile,
                'PATH'       => sprintf('./%s/migrations', $appDir),
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
                    empty($options['FIELDS']) && $options['FIELDS'] = array('column_name:column_type');
                    break;
                default:
                    $template = 'migrations/default.php.twig';
                    break;
            }

            if ($this->make($testFile, $template, $options))
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
