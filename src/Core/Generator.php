<?php
namespace Craftsman\Core;

use Craftsman\Core\Command;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Base Generator Class
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 * @version     1.0.0
 */
abstract class Generator extends Command
{
	/**
	 * @var \Filesystem
	 */
  protected $_filesystem;

	/**
	 * @var \Twig_Environment
	 */
	protected $_twig;

  /**
   * Class constructor
   */
  public function __construct()
  {
    parent::__construct();
    $this->_filesystem = new Filesystem();
		$loader = new \Twig_Loader_Filesystem(CRAFTSMANPATH.'utils/templates/');
		$this->_twig = new \Twig_Environment($loader);
  }

  /**
   * Command configuration method.
   * Configure all the arguments and options.
   */
  protected function configure()
  {
    parent::configure();
    $this
      ->addArgument(
        'filename',
        InputArgument::REQUIRED,
        'Generator filename'
      )
      ->addArgument(
        'options',
        InputArgument::IS_ARRAY,
        'Options passed to all generated files'
      )
      ->addOption(
        'path',
        'p',
        InputOption::VALUE_REQUIRED,
        'Set the generator base path',
        realpath(getenv('CI_APPPATH'))
      )
      ->addOption(
        'force',
        NULL,
        InputOption::VALUE_NONE,
        'If set, the task will force the generation process'
      );
    }

  /**
   * Generate files based on templates.
   *
   * @param  mixed  $filenames The file to be written to
   * @param  mixed  $paths     The Twig_Loader_Filesystem template path
   * @param  array  $options   The data to write into the file
   * @param  string $template  The template file
   * @return bool              Returns true if the file has been created
   */
  protected function make($filenames, $template = 'base.php.twig', array $options = array())
  {
    foreach ((array) $filenames as $filename)
    {
      if (! $this->getOption('force') && $this->_filesystem->exists($filename))
      {
        throw new \RuntimeException("Cannot duplicate [{$filename}].");
      }

			$reflection = new \ReflectionClass(get_class($this));

			if ($reflection->getShortName() === 'Migration')
      {
        $function = new \Twig_SimpleFunction('argument', function ($field = "") {
          return array_combine(array('name','type'), explode(':', $field));
        });
        $this->_twig->addFunction($function);
        foreach ($this->getArgument('options') as $option)
        {
          list($key, $value) = explode(':', $option);
          $options[$key] = $value;
        }
      }

			$this->_filesystem->dumpFile(
        $filename,
        $this->_twig->loadTemplate($template)->render($options)
      );
    }
    return TRUE;
  }
}
