<?php
namespace Craftsman\Commands\Migrations;

use Craftsman\Core\Migration;

/**
 * Migration\Latest Command
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 */
class Latest extends Migration implements \Craftsman\Interfaces\Command
{
	protected $name        = 'migrate:latest';
	protected $description = 'Run the latest migration';
	protected $aliases 		 = ['m:latest'];

	public function start()
	{
		$migrations = $this->migration->find_migrations();
		$version    = $this->migration->get_latest_version($migrations);
		$db_version = intval($this->migration->get_db_version());

		if($version == $db_version)
		{
			return $this->note('Database is up-to-date');
		}
		elseif ($version > $db_version)
		{
			$this->text(
				'Migrating database <info>UP</info> to version '
				.'<comment>'.$version.'</comment> from <comment>'.$db_version.'</comment>'
			);
			$case = 'migrating';
			$signal = '++';
		}
		else
		{
			$this->text(
				'Migrating database <info>DOWN</info> to version '
				.'<comment>'.$version.'</comment> from <comment>'.$db_version.'</comment>'
			);
			$case = 'reverting';
			$signal = '--';
		}

		$this->newLine();
		$this->text('<info>'.$signal.'</info> '.$case);

		$time_start = microtime(true);

		$this->migration->latest();

		$time_end = microtime(true);

		list($query_exec_time, $exec_queries) = $this->measureQueries($this->migration->db->queries);

		$this->summary($signal, $time_start, $time_end, $query_exec_time, $exec_queries);
	}
}
