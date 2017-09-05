<?php
namespace Craftsman\Commands\Migrations;

use Craftsman\Core\Migration;
use Symfony\Component\Console\Helper\TableSeparator;

/**
 * Migration\Info Command
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 */
class Info extends Migration implements \Craftsman\Interfaces\Command
{
    protected $name        = 'migrate:info';
    protected $description = 'Display the current migration scheme';
    protected $aliases     = ['m:info', 'db:info'];
    protected $harmless    = true;

    public function start()
    {
        $migrations     = $this->migration->find_migrations();
        $db_version     = $this->migration->get_db_version();
        $latest_version = $this->migration->get_latest_version($migrations);

        $this->newLine();

        $this->table(
            ['Name', 'Type', 'Database version'],
            [
                [
                    $this->migration->get_module_name(),
                    $this->migration->get_type(),
                    $db_version
                ]
            ]
        );

        $this->text(sprintf('Local version: %s', $latest_version));

        if ($latest_version < $db_version)
        {
            $this->caution('Could not find any migrations, check the migration path.');
        }
        elseif ($latest_version > $db_version)
        {
            $this->note("The Database is not up-to-date, run 'migrate:latest'");
        }
        else
        {
            $this->success('Database is up-to-date.');
        }
    }
}
