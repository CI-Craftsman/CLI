<?php
namespace Craftsman\Commands;

use Craftsman\Core\Command;
use Craftsman\Core\Codeigniter;
use Symfony\Component\Finder\Finder;

/**
 * Serve Command
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 */
class Notes extends Command
{
  protected $name        = 'notes';
  protected $description = 'Enumerate all annotations';

  /**
   * Command configuration method.
   * Configure all the arguments and options.
   */
  protected function configure()
  {
    parent::configure();
  }

  /**
   * Execute the console command.
   *
   * @return void
   * @throws \Exception
   */
  public function start()
  {
    $codeigniter = new Codeigniter();
    $CI =& $codeigniter->get();

    $finder = new Finder();
    $finder->ignoreUnreadableDirs()->files()->name('*.php')->in(APPPATH);

    $basepath = basename(APPPATH);

    $found = [];

    foreach ($finder as $file) {
      $_file = new \SplFileObject($file->getRealPath());
      foreach ($_file as $k => $line)
      {
        $exp = preg_match('/TODO|FIXME/', $line);
        if ($exp > 0)
        {
          $line = str_replace(['//','#','TODO:','FIXME:'], ['','','[TODO]','[FIXME]'], trim($line));
          $found[$file->getRelativePath().'/'. basename($file)][] = "[{$k}]{$line}";
        }
      }
    }
    if (empty($found))
    {
      $this->text('There are not notes marked.');
      $this->newLine();
    }
    else
    {
      foreach ($found as $file => $lines)
      {
        $this->text("{$basepath}/$file:");
        $this->newLine();
        $this->listing($lines);
      }
    }
  }
}
