<?php
namespace Craftsman\Commands;

use Craftsman\Core\Command;
use Symfony\Component\Process\ProcessUtils;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Serve Command
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 */
class Serve extends Command
{
  protected $name        = 'serve';
  protected $description = 'Serve the application on the PHP development server';

  /**
   * Command configuration method.
   * Configure all the arguments and options.
   */
  protected function configure()
  {
    parent::configure();

    $this
        ->addOption(
            'host',
            NULL,
            InputOption::VALUE_OPTIONAL,
            'The host address to serve the application on.',
            'localhost'
        )
        ->addOption(
            'port',
            NULL,
            InputOption::VALUE_OPTIONAL,
            'The port to serve the application on.',
            8000
        )
        ->addOption(
            'docroot',
            NULL,
            InputOption::VALUE_OPTIONAL,
            'Specify an explicit document root.',
            FALSE
        );
  }

  /**
   * Execute the console command.
   *
   * @return void
   * @throws \Exception
   */
  public function start()
  { 
    $host    = $this->getOption('host');
    $port    = intval($this->getOption('port'));
    $docpath = $this->getOption('docroot')? $this->getOption('docroot') : '.';

    $base    = ProcessUtils::escapeArgument(CRAFTSMANPATH);
    $binary  = ProcessUtils::escapeArgument((new PhpExecutableFinder)->find(false));
    $docroot = ProcessUtils::escapeArgument($docpath);

    $this->writeln("Codeigniter development server started at " . date(DATE_RFC2822));
    $this->writeln("Listening on http://{$host}:{$port}");
    $this->writeln("Document root is ". realpath($docpath));
    $this->writeln("Press Ctrl-C to quit.");

    $req_blacklist = ['EOF)'];

    try 
    {
      $process = new Process("{$binary} -S {$host}:{$port} {$base}/utils/server.php -t {$docroot}"); 

      $process
        ->setWorkingDirectory($docpath)
        ->setTimeout(0)
        ->setPTY(true)
        ->mustRun(function($type, $buffer) use ($req_blacklist) {      
          foreach (explode("\n", rtrim($buffer, "\n")) as $output) 
          {
            $req = substr(strrchr($output, ' '), 1);
            if (! in_array($req, $req_blacklist)) 
            {
              if ($response = (strpos($output, '[200]') !== FALSE)) 
              {
                $output = str_replace($req, "<info>{$req}</info>", $output);
              }
              $this->writeln($output); 
            }
          }
        });      
    } 
    catch (ProcessFailedException $e) 
    {
      throw new Exception("Error Processing Request: {$e->getMessage()}");
    }
  }
}
