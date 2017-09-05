<?php
namespace Craftsman\Commands;

use Craftsman\Core\Command;
use Craftsman\Core\Codeigniter;
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
    use \Craftsman\Traits\Migration\Info;

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
        $message = "WARNING! You are about to execute a database seed operation "
            ."that could result data lost.\n Do you wish to continue?";

        if (! $this->confirm($message))
        {
            $this->error('Process aborted!');
            exit(3);
        }
        try
        {
          $filename = ucfirst($this->getArgument('name'));
          $appPath  = realpath(getenv('APPPATH'));
          $appDir   = basename($appPath);

          if (file_exists($file = sprintf('%s/seeders/%s.php', $appPath, $filename)))
          {
            require_once $file;
          }
          else
          {
            throw new \RuntimeException('Seeder does not exist.');
          }

          $obj = new $filename(new Codeigniter);
          $obj->db->queries = [];

          if (! method_exists($obj, 'run'))
          {
            throw new \RuntimeException(sprintf(
              '[%s] Seeder class does not contain a Seeder::run method',
              $filename
            ));
          }

          $case = 'seeding';
          $signal = '++';

          $this->newLine();
          $this->text(sprintf('<info>%s</info> %s', $signal, $case));

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
            $this->text(sprintf('<comment>-></comment> %s', $queries[$i]));
            $query_exec_time += $times[$i];
            $exec_queries += 1;
        }
        return array($query_exec_time, $exec_queries);
    }
}
