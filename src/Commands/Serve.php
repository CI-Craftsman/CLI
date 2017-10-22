<?php
namespace Craftsman\Commands;

use Craftsman\Core\Command;
use Symfony\Component\Process\ProcessUtils;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
            'docroot',
            NULL,
            InputOption::VALUE_OPTIONAL,
            'Specify an explicit document root.',
            FALSE
        )
        ->addOption(
            'port',
            NULL,
            InputOption::VALUE_OPTIONAL,
            'The port to serve the application on.',
            8000
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
    try
    {
        $host    = $this->getOption('host');
        $port    = intval($this->getOption('port'));
        $docpath = $this->getOption('docroot')? $this->getOption('docroot') : '.';

        $binary  = ProcessUtils::escapeArgument((new PhpExecutableFinder)->find(false));
        $docroot = ProcessUtils::escapeArgument($docpath);

        $this->writeln([
          sprintf('Codeigniter development server started at %s', date(DATE_RFC2822)),
          sprintf('Listening on http://%s:%s', $host, $port),
          sprintf('Document root is %s', realpath($docpath)),
          'Press Ctrl-C to quit.'
        ]);

        $process = new Process(sprintf('%s -S %s:%s -t %s', $binary, $host, $port, $docroot));
        $output  = $this->output;

        $process
            ->setWorkingDirectory($docpath)
            ->setTimeout(0)
            ->setPTY(true)
            ->run(function ($type, $buffer) use($output) {
                if (Process::ERR === $type && $output instanceof ConsoleOutputInterface) {
                    $output = $output->getErrorOutput();
                }
                $output->write($buffer, false, OutputInterface::OUTPUT_RAW);
            });
    }
    catch (ProcessFailedException | Exception $e)
    {
        $this->error(sprintf(
            'Error starting the server: %s', $e->getMessage()
        ));
    }
  }
}
