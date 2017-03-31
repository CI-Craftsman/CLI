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
		$this->fh = fopen('php://stdout','a'); //both (a)ppending, and (w)riting will work
		fwrite($this->fh, "\n");
	}

	public function __destruct()
	{
		fclose($this->fh); 
	}

	public function request()
	{
		$from 		= $this->CI->uri->uri_string();
		$index_page = $this->CI->config->item('index_page');
		$namespace 	= (! empty($index_page))? "/{$index_page}" : NULL;
		$date 		= date('D M t h:i:s Y'); 
		$pid 		= getmypid();
		$status 	= http_response_code();

		fwrite($this->fh, "[{$date}] ::{$pid} [{$status}]: <info>{$namespace}/{$from}</info>");
	}

	public function queries()
	{
		foreach (get_object_vars($this->CI) as $name => $cobject)
		{
			if (is_object($cobject))
			{
				if ($cobject instanceof \CI_DB)
				{
					$dbs[get_class($this->CI).':$'.$name] = $cobject;
				}
				elseif ($cobject instanceof \CI_Model)
				{
					foreach (get_object_vars($cobject) as $mname => $mobject)
					{
						if ($mobject instanceof \CI_DB)
						{
							$dbs[get_class($cobject).':$'.$mname] = $mobject;
						}
					}
				}
			}
		}

		if (isset($dbs)) 
		{
			foreach ($dbs as $name => $db) 
			{
				foreach ($db->queries as $key => $val)
				{
					$time = number_format($db->query_times[$key], 4);
					$val = trim(preg_replace('/\s+/', ' ', $val));	

					fwrite($this->fh, "<fg=cyan;bg=black>{$name} ({$time})</> <options=bold>{$val}</>\n");
				}			
			}
		}
	}

	public static function hook()
	{
		$repeater = rtrim(str_repeat('../', substr_count(APPPATH, '/')),'/');
		return [
			'pre_controller' => [
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
}