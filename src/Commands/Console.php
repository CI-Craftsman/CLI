<?php
namespace Craftsman\Commands;

use Craftsman\Core\Command;
use Craftsman\Core\Codeigniter;
use Psy\Configuration;
use Psy\Shell;

/**
 * Console Command
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 */
class Console extends Command
{
    protected $name        = 'console';
    protected $description = 'Interact with your application';
    protected $aliases     = ['c'];

    protected $commandWhitelist = [
        'generate:controller',
        'generate:migration',
        'generate:model',
        'generate:seeder',
        'migrate:check',
        'migrate:latest',
        'migrate:refresh',
        'migrate:reset',
        'migrate:rollback',
        'migrate:version',
        'db:seed',
        'serve'
    ];

  /**
   * Execute the console command.
   *
   * @return void
   * @throws \Exception
   */
  public function start()
  {
      $this->getApplication()->setCatchExceptions(false);

      try
      {
          // Create a Codeigniter instance
          $CI = new Codeigniter; $CI =& $CI->get();

          $config = new Configuration; // TODO: Create a method that configures the Psy\Shell
          $shell  = new Shell($config);

          $this->writeln([
            sprintf('Craftsman %s - Console',$this->getApplication()->getVersion()),
            str_repeat('-', 60),
            'Codeigniter : $CI',
            sprintf('App Path: ./%s/', basename(APPPATH)),
            str_repeat('-', 60)
          ]);

          $shell->setScopeVariables(['CI' => $CI]);
          $shell->addCommands($this->getCommands());
          $shell->run();
      }
      catch (Exception $e)
      {
          echo $e->getMessage() . PHP_EOL;
          // TODO: this triggers the "exited unexpectedly" logic in the
          // ForkingLoop, so we can't exit(1) after starting the shell...
          // exit(1);
      }
  }

    private function getCommands()
    {
        $commands = [];
        foreach ($this->getApplication()->all() as $name => $command)
        {
            in_array($name, $this->commandWhitelist) && $commands[] = $command;
        }
        return $commands;
    }
}
