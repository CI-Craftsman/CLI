<?php
namespace Craftsman\Hooks;

/**
*
*/
class Reporter
{
    private $CI;
    private $fh;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->fh = fopen('php://stdout', 'a'); //both (a)ppending, and (w)riting will work
    }

    public function request()
    {
        $date    = date('D M t h:i:s Y', $_SERVER['REQUEST_TIME']);
        $addr    = $_SERVER['REMOTE_ADDR'];
        $port    = $_SERVER['REMOTE_PORT'];
        $status  = http_response_code();
        $uri     = $_SERVER['REQUEST_URI'];

        fwrite($this->fh, "[{$date}] {$addr}:{$port} [{$status}]: <info>{$uri}</info>\n");
    }

    public function queries()
    {
        foreach (get_object_vars($this->CI) as $name => $cobject) {
            if (is_object($cobject)) {
                if ($cobject instanceof \CI_Model) {
                    foreach (get_object_vars($cobject) as $mname => $mobject) {
                        if ($mobject instanceof \CI_DB) {
                            $dbs[get_class($cobject).':$'.$mname] = $mobject;
                        }
                    }
                } elseif ($cobject instanceof \CI_DB) {
                    $dbs[get_class($this->CI).':$'.$name] = $cobject;
                }
            }
        }

        if (isset($dbs)) {
            foreach ($dbs as $name => $db) {
                foreach ($db->queries as $key => $val) {
                    $time = number_format($db->query_times[$key], 4);
                    $val = trim(preg_replace('/\s+/', ' ', $val));

                    fwrite($this->fh, "<fg=cyan;bg=black>{$name} ({$time})</> <options=bold>{$val}</>\n");
                }
            }
        }
    }

    public static function hook()
    {
        $repeater = rtrim(str_repeat('../', substr_count(APPPATH, '/')), '/');
        return [
            'post_controller_constructor' => [
                'class'    => '\Craftsman\Hooks\Reporter',
                'function' => 'request',
                'filename' => 'Reporter.php',
                'filepath' => "{$repeater}".__DIR__
            ],
            'post_controller' => [
                'class'    => '\Craftsman\Hooks\Reporter',
                'function' => 'queries',
                'filename' => 'Reporter.php',
                'filepath' => "{$repeater}".__DIR__
            ]
        ];
    }

    public function __destruct()
    {
        fclose($this->fh);
    }
}
