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
		$tables        = $config->tables;

		$groups = [
			[
				// 'id'					=> 1,
				'guid'				=> '5bb7a9e7db3e29a2032bd1c5010561ff',
				'name'        => 'admin',
				'description' => 'Administrator',
			],
			[
				// 'id'					=> 2,
				'guid'				=> '342bf19ff862494828bfa7c8cb20926a',
				'name'        => 'members',
				'description' => 'General User',
			],
		];
		$this->db->table($tables['groups'])->insertBatch($groups);

	}
}
