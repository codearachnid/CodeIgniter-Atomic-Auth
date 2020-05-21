<?php namespace AtomicAuth\Database\Seeds;

/**
 * @package CodeIgniter-Atomic-Auth
 */

class AtomicAuthGroupsSeeder extends \CodeIgniter\Database\Seeder
{
	public function run()
	{
		$config = config('AtomicAuth\\Config\\AtomicAuth');
		$this->DBGroup = empty($config->databaseGroupName) ? '' : $config->databaseGroupName;
		$tables        = $config->tables;

		$users = [
			[
				'ip_address'              => '127.0.0.1',
				'username'                => 'administrator',
				'password'                => '$2y$08$200Z6ZZbp3RAEXoaWcMA6uJOFicwNZaqk4oDhqTUiFXFe63MG.Daa',
				'email'                   => 'admin@admin.com',
				'activation_code'         => '',
				'forgotten_password_code' => null,
				'active'                  => '1',
			],
		];
		$this->db->table($tables['users'])->insertBatch($users);

		$usersGroups = [
			[
				'user_id'  => '1',
				'group_id' => '1',
			],
			[
				'user_id'  => '1',
				'group_id' => '2',
			],
		];
		$this->db->table($tables['users_groups'])->insertBatch($usersGroups);
	}
}
