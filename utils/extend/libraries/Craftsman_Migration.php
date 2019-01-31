<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Craftsman Migration
 *
 * Run all the posible migration escenarios in a Codeigniter or HMVC application.
 *
 * @package		CodeIgniter
 * @category	Libraries
 * @author		David Sosa Valdes
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 */
class Craftsman_Migration extends \CI_Migration
{
	/**
	 * Module path where migrations are stored.
	 * @var string
	 */
	protected $_module_path;

	/**
	 * Module name stored in Database.
	 * @var string
	 */
	protected $_module_name = 'ci_system';

	/**
	 * Class Constructor
	 *
	 * @param array $config Migration library config arguments.
	 */
	public function __construct($config = array())
	{
       	parent::__construct($config);

		if (! $this->db->field_exists('module', $this->_migration_table))
		{
			$fields = array(
        		'module' => array(
        			'type' => 'VARCHAR',
        			'first' => TRUE,
        			'constraint' => '100'
        		)
			);
			$this->dbforge->add_column($this->_migration_table, $fields);

			$this->dbforge->add_key('module', TRUE);
			
			$this->db->query("UPDATE {$this->_migration_table} SET module = '{$this->_module_name}' LIMIT 1;");
		}
		$this->_set_migration_path();

		log_message('info', 'Craftsman Migration Class Init');
	}

	/**
	 * Set alll params you want.
	 *
	 * @param array $config
	 */
	public function set_params($config = array())
	{
		foreach ($config as $key => $val)
		{
			$this->{'_'.$key} = $val;
		}
		$this->_set_migration_path();
		return $this;
	}

	/**
	 * Get module name
	 *
	 * @return string
	 */
	public function get_module_name()
	{
		return $this->_module_name;
	}

	/**
	 * Get module path
	 *
	 * @return string
	 */
	public function get_module_path()
	{
		return $this->_migration_path;
	}

	/**
	 * Get migration type
	 *
	 * @return string
	 */
	public function get_type()
	{
		return $this->_migration_type;
	}

	/**
	 * Get the actual db migration version
	 *
	 * @return mix the migration number version
	 */
	protected function _get_version()
	{
		$this->db->where('module', $this->_module_name);
		$row = $this->db->get($this->_migration_table)->row();
		return (!is_null($row)) ? $row->version : '0';
	}

	/**
	 * Get the actual db migration version
	 *
	 * @return mix the migration number version
	 */
	public function get_db_version()
	{
		return abs($this->_get_version());
	}

	/**
	 * Get the migration number on config file.
	 *
	 * @return mix the migration number version
	 */
	public function get_config_version()
	{
		return abs($this->_migration_version);
	}

	/**
	 * Get migration number based on a set of migrations
	 *
	 * @param  mix $number
	 * @return mix the migration number version
	 */
	public function get_latest_version(array $migrations)
	{
		$number = basename(end($migrations));
		return abs($this->_get_migration_number($number));
	}

	/**
	 * Set the correct migration path
	 */
	private function _set_migration_path()
	{
		if ($this->_module_name === 'ci_system') {
			return;
		}
		elseif ($this->_module_path !== NULL)
		{
			$this->_migration_path = rtrim($this->_module_path,'/').'/';
		}
		return $this;
	}

	/**
	 * Store the current schema version or ignore it without changes.
	 *
	 * @param string $migration	Migration reached
	 */
	protected function _update_version($migration)
	{
		$data = array(
			'version' => $migration,
			'module' => $this->_module_name
		);
		if ($this->_check_module_exist())
		{
			$this->db->where('module', $this->_module_name);
			$this->db->update($this->_migration_table, $data);
		}
		else
		{
			$insert_query = $this->db->insert_string($this->_migration_table, $data);
			$insert_query = str_replace('INSERT INTO','INSERT IGNORE INTO', $insert_query);

			$this->db->query($insert_query);
		}
		return $this;
	}

	/**
	 * Check if migration module exist in db.
	 *
	 * @return bool
	 */
	private function _check_module_exist()
	{
		$this->db->from($this->_migration_table);
		$this->db->where('module', $this->_module_name);
		$this->db->limit(1);
		$query = $this->db->get();
		return $query->num_rows() >= 1;
	}

	/**
	 * Get current migration table used in Database.
	 *
	 * @return string migration table name
	 */
	public function getTable()
	{
		return $this->_migration_table;
	}
}

/* End of file MY_Migration.php */
