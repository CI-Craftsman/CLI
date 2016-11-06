<?php
/**
 * Part of Craftsman Library
 *
 * @author     David Sosa Valdes <https://github.com/davidsosavaldes>
 * @license    MIT License
 * @copyright  2016 David Sosa Valdes
 * @link       https://github.com/davidsosavaldes/Craftsman
 *
 * Based on https://raw.githubusercontent.com/kenjis/codeigniter-ss-twig/master/ci_instance.php
 */

define('ENVIRONMENT', getenv('CI_ENV') ? getenv('CI_ENV') : 'development');

$system_path        = getenv('CI_SYSTEMPATH');
$application_folder = getenv('CI_APPPATH');
$public_folder      = getenv('CI_FCPATH');

if ($_temp = realpath($system_path))
{
  $system_path = $_temp;
}

$system_path = rtrim($system_path, '/').'/';

if (! is_dir($system_path))
{
	print("Your system folder path does not appear to be set correctly.\n");
	exit(3);
}

define('BASEPATH', str_replace("\\", "/", $system_path));

if (is_dir($application_folder))
{
	if ($_temp = realpath($application_folder))
	{
		$application_folder = $_temp;
	}
	define('APPPATH', $application_folder.'/');
}
else
{
	if ( ! is_dir(BASEPATH.$application_folder.'/'))
	{
		print('Your application folder path does not appear to be set correctly.');
		exit(3);
	}
	define('APPPATH', BASEPATH.$application_folder.'/');
}

define('VIEWPATH', $application_folder . '/views/');
define('FCPATH', $public_folder . '/');

require(BASEPATH . 'core/Common.php');

if (file_exists(APPPATH . 'config/' . ENVIRONMENT . '/constants.php'))
{
  require(APPPATH . 'config/' . ENVIRONMENT . '/constants.php');
}
else
{
  require(APPPATH . 'config/constants.php');
}

# Make sure some config variables are set correctly
get_config(array(
	'subclass_prefix' => 'Craftsman_',
));

$charset = strtoupper(config_item('charset'));
ini_set('default_charset', $charset);

if (extension_loaded('mbstring'))
{
  define('MB_ENABLED', TRUE);
  // mbstring.internal_encoding is deprecated starting with PHP 5.6
  // and it's usage triggers E_DEPRECATED messages.
  @ini_set('mbstring.internal_encoding', $charset);
  // This is required for mb_convert_encoding() to strip invalid characters.
  // That's utilized by CI_Utf8, but it's also done for consistency with iconv.
  mb_substitute_character('none');
}
else
{
  define('MB_ENABLED', FALSE);
}

// There's an ICONV_IMPL constant, but the PHP manual says that using
// iconv's predefined constants is "strongly discouraged".
if (extension_loaded('iconv'))
{
  define('ICONV_ENABLED', TRUE);
  // iconv.internal_encoding is deprecated starting with PHP 5.6
  // and it's usage triggers E_DEPRECATED messages.
  @ini_set('iconv.internal_encoding', $charset);
}
else
{
  define('ICONV_ENABLED', FALSE);
}

load_class('Config', 'core');
load_class('Utf8', 'core');
load_class('Security', 'core');
// load_class('Router', 'core');
load_class('Input', 'core');
load_class('Lang', 'core');

require_once(BASEPATH . 'core/Controller.php');

if (!function_exists('get_instance'))
{
  function &get_instance()
  {
    return \CI_Controller::get_instance();
  }
}

return new \CI_Controller();
