<?php
namespace Craftsman\Commands\Database;

use Craftsman\Core\Command;
use Symfony\Component\Console\Input\InputArgument;
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
class Seeder extends Command
{
    protected $name        = 'db:seed';
    protected $description = 'Seed database with test data';

    /**
     * Command configuration method.
     * Configure all the arguments and options.
     */
    protected function configure()
    {
    	parent::configure();

        $this
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Seeder filename'
            )
            ->addOption(
                'path',
                NULL,
                InputOption::VALUE_REQUIRED,
                'Set the migration path',
                'application/seeders/'
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
        $message = 'WARNING! You are about to execute a database seed operation that could '
          .'result data lost. Do you wish to continue?';

        if (! $this->confirm($message))
        {
            $this->error('Process aborted!');
            exit(3);
        }
        try
        {
            $name = ucfirst($this->getArgument('name'));
            $path = rtrim($this->getOption('path'),'/').'/';

            if (file_exists($file = $path . $name . '.php'))
            {
                require_once $file;
            }
            elseif (file_exists($file = APPPATH.'seeders/'.$name.'.php'))
            {
                require_once $file;
            }
            else
            {
                throw new \RuntimeException("Seeder does not exist.");
            }

            $obj = new $name();

            if (! method_exists($obj, 'run'))
            {
                throw new \RuntimeException("{$name} Seeder class does not contain a Seeder::run method");
            }

            $case = 'seeding';
            $signal = '++';

            $this->newLine();
            $this->text('<info>'.$signal.'</info> '.$case);

            $time_start = microtime(true);

            $obj->run();

            $time_end = microtime(true);

            list($query_exec_time, $exec_queries) = $this->measureQueries($obj->db->queries, $obj->db->query_times);

            $this->summary($signal, $time_start, $time_end, $query_exec_time, $exec_queries);

        }
        catch (\Exception $e)
        {
            $this->error($e->getMessage());
        }
    }

    /**
     * Measure the queries execution time and show in console
     *
     * @param  array  $queries  CI Database queries
     * @param  array  $times    CI Database query execution times
     * @return array            Returns a set of total exec time and the amount of queries.
     */
    protected function measureQueries(array $queries, array $times)
    {
        $query_exec_time = 0;
        $exec_queries    = 0;

        $this->newLine();

        for ($i = 0; $i < count($queries); $i++)
        {
            $this->text('<comment>-></comment> '.$queries[$i]);
            $query_exec_time += $times[$i];
            $exec_queries += 1;
        }
        return array($query_exec_time, $exec_queries);
    }

    /**
     * Display in the console all the processes
     *
     * @param  string $signal           Migration command signal (++,--)
     * @param  float  $time_start       Unix timestamp with microseconds from the start of process
     * @param  float  $time_end         Unix timestamp with microseconds from the end of process
     * @param  float  $query_exec_time  Queries execution time in seconds
     * @param  int    $exec_queries     Amount of executed queries
     */
    protected function summary($signal = NULL, $time_start, $time_end, $query_exec_time, $exec_queries)
    {
        $this->newLine();
        $this->text('<info>'.$signal.'</info> query process ('.number_format($query_exec_time, 4).'s)');
        $this->newLine();
        $this->text('<comment>'.str_repeat('-', 30).'</comment>');
        $this->newLine();
        $execution_time = ($time_end - $time_start);
        $this->text('<info>++</info> finished in '. number_format($execution_time, 4).'s');
        $this->text('<info>++</info> '.$exec_queries.' sql queries');
    }
}
