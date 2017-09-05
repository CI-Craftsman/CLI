<?php
namespace Craftsman\Core;

use Craftsman\Core\Command;
use Craftsman\Core\Codeigniter;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base Migration Class
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 * @version     1.0.0
 */
abstract class Migration extends Command
{
    use \Craftsman\Traits\Migration\Info;

    /**
     * Codeigniter migration class.
     * @var \CI_Migration
     */
    protected $migration;

    /**
     * Harmless mode - without confirm the action.
     * @var boolean
     */
    protected $harmless = false;

    /**
     * Configure all the arguments and options.
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this
        ->addOption(
          'name',
          null,
          InputOption::VALUE_REQUIRED,
          'Set the migration version name',
          false
        )
        ->addOption(
          'sequential',
          null,
          InputOption::VALUE_NONE,
          'If set, the migration will run with sequential mode active'
        );
    }

    /**
     * Execute the Migration command
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $appPath = realpath(getenv('CI_APPPATH'));
        // Create a Codeigniter instance
        $CI = new Codeigniter; $CI =& $CI->get();
        // Add the Craftsman extended packages
        $CI->load->add_package_path(CRAFTSMANPATH.'utils/extend/');
        // Load the special migration settings
        $CI->config->load('migration', true, true);

        $this->text(sprintf('(in ./%s)', basename(getcwd())));

        if ($params = $CI->config->item('migration'))
        {
            $this->newLine();

            if ($this->getOption('sequential') !== false)
            {
                $params['migration_type'] = 'sequential';
            }

            $CI->load->library('migration', $params);

            $this->migration =& $CI->migration;
            $this->migration->db->queries = [];

            $this->text(
                sprintf(
                  'Migration directory: %s/%s/',
                  basename(APPPATH),
                  basename($this->migration->get_module_path())
                )
            );

            if (! $this->harmless)
            {
                $this->note(
                    'You are about to execute a database migration'
                    .'that could result in schema changes and data lost.'
                );

                if (! $this->confirm('Do you wish to continue?'))
                {
                    $this->error('Process aborted!');
                    exit(3);
                }
            }
        }
        else
        {
            throw new \RuntimeException(
              'Craftsman migration settings does not'
              .' appear to set correctly.'
            );
        }

        // Set Codeigniter Craftsman Migration Library arguments
        $params = [
            'module_path' => sprintf('%s/migrations/', $appPath),
        ];

        if ($this->getOption('name') !== FALSE)
        {
            $params['module_name'] = $this->getOption('name');
        }
        else
        {
            $moduleName = strtolower(basename($appPath));

            if ($moduleName !== 'application')
            {
                $params['module_name'] = $moduleName;
            }
        }

        $this->migration->set_params($params);

        parent::execute($input, $output);
    }

    /**
     * Get the signal message
     *
     * @param int $signal Migration signal (++, --)
     * @param int $case   Migration case
     * @return string
     */
    public function getSignalMessage($signal, $case)
    {
        return sprintf('<info>%s</info> %s', $signal, $case);
    }

    /**
     * Get the migration message
     *
     * @param  string $status     Migration status (UP/DOWN)
     * @param  int    $version    Migration version in the APP Filesystem
     * @param  int    $db_version Migration version stored in the database
     * @return string
     */
    public function getMigrationMessage($status, $version, $db_version)
    {
        return sprintf(
            'Migrating database <info>%s</info> to version %s'
            .'<comment>%s</comment> from <comment>%s</comment>',
            $status, $version, $db_version
        );
    }
}
