<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 */

$uri = urldecode(
  parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Codeigniter
// application without having installed a "real" web server software here.
if ($uri !== '/' && file_exists(getcwd().$uri))
{
  return false;
}

if (! file_exists($codeigniter = getcwd().'/index.php'))
{
  print("Your codeigniter 'index.php' path does not appear to be set correctly.\n");
  exit(3);
}
else
{
  require_once $codeigniter;
}
