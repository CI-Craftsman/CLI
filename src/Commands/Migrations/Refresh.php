<?php
namespace Craftsman\Commands\Migrations;

use Craftsman\Core\Migration;

/**
 * Migration\Refresh Command
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 */
class Refresh extends Migration implements \Craftsman\Interfaces\Command
{
    protected $name        = 'migrate:refresh';
    protected $description = 'Rollback all migrations and run them all again';
    protected $aliases     = ['m:refresh'];

    public function start()
    {
        $migrations = $this->migration->find_migrations();
        $db_version = intval($this->migration->get_db_version());
        $version    = 0;
        $case       = 'refreshing';
        $signal     = '--';

        $this->newLine();

        $this->text($this->getSignalMessage($signal, $case));

        $this->text($this->getMigrationMessage('DOWN', $version, $db_version));

        $time_start = microtime(true);

        $this->migration->version($version);

        $this->text($this->getMigrationMessage('UP', 'LATEST', $db_version));

        $case   = 'migrating';
        $signal = '++';

        $this->migration->latest();

        $time_end = microtime(true);

        list($query_exec_time, $exec_queries) = $this->measureQueries($this->migration->db->queries);

        $this->summary($signal, $time_start, $time_end, $query_exec_time, $exec_queries);
    }
}
