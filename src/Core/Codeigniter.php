<?php
namespace Craftsman\Core;

/**
 * Codeigniter Class
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 * @version     1.0.0
 */
class Codeigniter
{
    public function __construct()
    {
        try
        {
            return require_once __DIR__.'/../../utils/codeigniter.php';
        }
        catch (Exception $e)
        {
            exit(preg_replace('/\s+/', ' ', $e->getMessage()));
        }
    }

    public function &get()
    {
        return \CI_Controller::get_instance();
    }
}
