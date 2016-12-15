<?php
namespace Craftsman\Commands\General;

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
    $docpath = realpath($this->getOption('docroot')? $this->getOption('docroot'): '.');

    $base    = ProcessUtils::escapeArgument(CRAFTSMANPATH);
    $binary  = ProcessUtils::escapeArgument((new PhpExecutableFinder)->find(false));
    $docroot = ProcessUtils::escapeArgument($docpath);

    $this->text("Codeigniter development server started on http://{$host}:{$port}/");

    $command = "{$binary} -S {$host}:{$port} {$base}/utils/server.php";
    $docpath && $command.=' -t {$docroot}';

    $process = new Process($command);
    $docpath && $process->setWorkingDirectory($docpath);
    $process->setTimeout(0);
    $process->run();

    if (! $process->isSuccessful())
    {
      throw new ProcessFailedException($process);
    }
  }
}
