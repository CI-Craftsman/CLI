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
     * @var object
     */
    protected $migration;

    /**
     * Harmless mode - without confirm the action.
     * @var boolean
     */
    protected $harmless = false;

    /**
     * @var \CI_Controller
     */
    protected $CI;

    /**
     * Command configuration method.
     * Configure all the arguments and options.
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
     * @param  InputInterface  $input  [description]
     * @param  OutputInterface $output [description]
     * @return [type]                  [description]
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Create a Codeigniter instance
        $this->CI =& (new Codeigniter)->get();
        // Add the Craftsman extended packages
        $this->CI->load->add_package_path(CRAFTSMANPATH.'utils/extend/');
        // Load the special migration settings
        $this->CI->config->load('migration', true, true);

        $appRoot = realpath(getenv('CI_APPPATH'));

        $this->text(sprintf('(in ./%s)', basename(getcwd())));

        if ($params = $this->CI->config->item('migration'))
        {
            $this->newLine();

            ($this->getOption('sequential') !== false)
                && $params['migration_type'] = 'sequential';

            $this->CI->load->library('migration', $params);

            $this->migration = $this->CI->migration;
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
                $this->note('You are about to execute a database migration that could'
                      .' result in schema changes and data lost');

                if (! $this->confirm('Do you wish to continue?'))
                {
                    $this->error('Process aborted!');
                    exit(3);
                }
            }
        }
        else
        {
            throw new \RuntimeException('Craftsman migration settings does not appear to set correctly.');
        }

        $this->setModelArguments();

        parent::execute($input, $output);
    }

    /**
     * Set Codeigniter Craftsman Migration Library arguments
     */
    protected function setModelArguments()
    {
        $appPath = realpath(getenv('CI_APPPATH'));

        $params = array(
            'module_path' => sprintf('%s/migrations/', $appPath)
        );

        if ($this->getOption('name') !== FALSE)
        {
            $params['module_name'] = $this->getOption('name');
        }
        else
        {
            if (($module_name = strtolower(basename($appPath))) !== 'application')
            {
                $params['module_name'] = $module_name;
            }
        }
        return $this->migration->set_params($params);
    }
}
