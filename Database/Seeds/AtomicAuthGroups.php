<?php namespace AtomicAuth\Database\Seeds;

/**
 * @package CodeIgniter-Atomic-Auth
 */

class AtomicAuthGroups extends \CodeIgniter\Database\Seeder
{
	public function run()
	{
		$config = config('AtomicAuth\\Config\\AtomicAuth');
		$this->DBGroup = empty($config->databaseGroupName) ? '' : $config->databaseGroupName;

		$groups = [
			[
				// 'id'					=> 1,
				'guid'				=> '5bb7a9e7db3e29a2032bd1c5010561ff',
				'name'        => 'admin',
				'description' => 'Administrator',
				'status'			=> 'active',
			],
			[
				// 'id'					=> 2,
				'guid'				=> '342bf19ff862494828bfa7c8cb20926a',
				'name'        => 'default',
				'description' => 'Default User',
				'status'			=> 'active',
			],
		];

		$this->db->table($config->tables['groups'])->insertBatch($groups);

	}
}
