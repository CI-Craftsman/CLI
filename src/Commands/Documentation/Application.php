<?php
namespace Craftsman\Commands\Documentation;

use Sami\Console\Command\UpdateCommand;
use Craftsman\Core\Codeigniter;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Sami\Sami;

/**
 * Serve Command
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 */
class Application extends UpdateCommand
{
  protected $name        = 'doc:app';
  protected $description = 'Parses then renders a project documentation';

  /**
   * Command configuration method.
   * Configure all the arguments and options.
   */
  protected function configure()
  {
    $this
        ->setName($this->name)
        ->setDescription($this->description)
        ->setDefinition(new InputDefinition([
          new InputOption('only-version', '', InputOption::VALUE_REQUIRED, 'The version to build'),
          new InputOption('force', '', InputOption::VALUE_NONE, 'Forces to rebuild from scratch', null)
        ]));
  }

  /**
   */
  protected function initialize(InputInterface $input, OutputInterface $output)
  {
      $this->input = $input;
      $this->output = $output;

      $codeigniter = new Codeigniter();
      $CI =& $codeigniter->get();

      $this->sami = new \Sami\Sami(APPPATH, [
        'title' => 'Codeigniter Application',
        'build_dir' => FCPATH.'docs/',
        'cache_dir' => FCPATH.'docs/cache',
        // 'simulate_namespaces' => TRUE
      ]);

      if ($input->getOption('only-version')) {
          $this->sami['versions'] = $input->getOption('only-version');
      }

      if (! $this->sami instanceof Sami) {
          throw new \RuntimeException(sprintf('Configuration file "%s" must return a Sami instance.', $config));
      }
  }
}
