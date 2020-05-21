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
				'guid'				=> 'e7cb8966-b553-4ee1-8bfe-cb2b873697ff',
				'name'        => 'admin',
				'description' => 'Administrator',
			],
			[
				// 'id'					=> 2,
				'guid'				=> '1b351ef4-c395-455e-bd0c-455a7d80781b',
				'name'        => 'members',
				'description' => 'General User',
			],
		];
		$this->db->table($tables['groups'])->insertBatch($groups);

	}
}
