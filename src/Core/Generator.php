<?php
namespace Craftsman\Core;

use Craftsman\Core\Command;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use \Twig_Loader_Filesystem as Twig_Loader;
use \Twig_Environment;

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
    protected $fs;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

  	/**
   	* Class constructor
   	*/
  	public function __construct()
  	{
      	parent::__construct();

        $templatesPath = sprintf('%s/utils/templates', rtrim(CRAFTSMANPATH));

      	$this->fs   = new Filesystem();
      	$this->twig = new Twig_Environment(new Twig_Loader($templatesPath));
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
	        'force',
	        null,
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
	          if (! $this->getOption('force') && $this->fs->exists($filename))
						{
	              throw new \RuntimeException(sprintf('Cannot duplicate %s', $filename));
	          }

	          $reflection = new \ReflectionClass(get_class($this));

	          if ($reflection->getShortName() === 'Migration')
						{
	              $this->twig->addFunction(new \Twig_SimpleFunction('argument',
                  function ($field = "") {
		                return array_combine(array('name','type'), explode(':', $field));
		              }
                ));

	              foreach ($this->getArgument('options') as $option)
								{
	                  list($key, $value) = explode(':', $option);
	                  $options[$key] = $value;
	              }
	          }

            $output = $this->twig->loadTemplate($template)->render($options);

	          $this->fs->dumpFile($filename, $output);
	      }
	      return true;
	  }

    /**
	   * Create a directory if doesn't exists.
     *
     * @param string $dirPath Directory path
     * @return null
	   */
    public function createDirectory($dirPath)
    {
      if (! $this->fs->exists($dirPath))
      {
        $this->fs->mkdir($dirPath);
      }
    }
}
