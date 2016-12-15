<?php
namespace Craftsman\Commands\Migrations;

use Craftsman\Core\Migration;

/**
 * Migration\Rollback Command
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 */
class Rollback extends Migration implements \Craftsman\Interfaces\Command
{
	protected $name        = 'migrate:rollback';
	protected $description = 'Rollback from the last migration';
	protected $aliases 		 = ['m:rollback'];

	public function start()
	{
		$migrations = $this->migration->find_migrations();
		$versions   = array_map('intval', array_keys($migrations));
		$db_version = intval($this->migration->get_db_version());

		end($versions);

		while ($version = prev($versions))
		{
			if ($version !== $db_version) { break; }
		}

		if(($version + $db_version) <= 0)
		{
			return $this->note("Can't rollback anymore");
		}
		else
		{
			$version === FALSE && $version = 0;

			$this->text('Migrating database <info>DOWN</info> to version '
				.'<comment>'.$version.'</comment> from <comment>'.$db_version.'</comment>');
			$case = 'reverting';
			$signal = '--';
		}

		$this->newLine();
		$this->text('<info>'.$signal.'</info> '.$case);

		$time_start = microtime(true);

		$this->migration->version($version);

		$time_end = microtime(true);

		list($query_exec_time, $exec_queries) = $this->measureQueries($this->migration->db->queries);

		$this->summary($signal, $time_start, $time_end, $query_exec_time, $exec_queries);
	}
}
