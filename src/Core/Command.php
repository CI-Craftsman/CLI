<?php
namespace Craftsman\Core;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command Class
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 * @version     1.0.0
 */
abstract class Command extends SymfonyCommand
{
    /**
     * Console command name
     * @var string
     */
    protected $name;

    /**
     * Console command description
     * @var string
     */
    protected $description;

    /**
     * Console command aliases
     * @var string[]
     */
    protected $aliases = [];

    /**
     * InputInterface instance
     * @var object
     */
    protected $_input;

    /**
     * OutputInterface instance
     * @var object
     */
    protected $_output;

    /**
     * Style instance
     * @var object
     */
    protected $_style;

    /**
     * Configure default attributes
     */
    protected function configure()
    {
        $this
            ->setName($this->name)
            ->setDescription($this->description)
            ->setAliases($this->aliases);
    }

    /**
     * Initialize the objects
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->_input  = $input;
        $this->_output = $output;
        $this->_style  = new SymfonyStyle($input, $output);
    }

    /**
     * Execute the command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try
        {
            if (method_exists($this, 'start'))
            {
                $this->start();
            }
            else
            {
                throw new \RuntimeException("Command is not set correctly.");
            }
        }
        catch (\Exception $e)
        {
            $this->error($e->getMessage());
        }
    }

    /**
     * Call some methods easily
     *
     * @param  string $name
     * @param  mixed  $arguments
     */
    public function __call($name = '', $arguments = NULL)
    {
        switch ($name)
        {
            case 'title':
            case 'section':
            case 'text':
            case 'listing':
            case 'table':
            case 'newLine':
            case 'note':
            case 'caution':
            case 'progressStart':
            case 'progressAdvance':
            case 'progressFinish':
            case 'ask':
            case 'askHidden':
            case 'confirm':
            case 'choice':
            case 'success':
            case 'warning':
            case 'error':
            case 'comment':
              return call_user_func_array(array($this->_style, $name), $arguments);
            case 'getArgument':
              return call_user_func_array(array($this->_input,'getArgument'), $arguments);
            case 'getOption':
              return call_user_func_array(array($this->_input, 'getOption'), $arguments);
            default:
              throw new \Exception("Codeigniter\Command: [{$name}] method not found.");

        }
    }
}
