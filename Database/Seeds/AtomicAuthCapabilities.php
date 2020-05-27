<?php namespace AtomicAuth\Database\Seeds;

/**
 * @package CodeIgniter-Atomic-Auth
 */

class AtomicAuthCapabilities extends \CodeIgniter\Database\Seeder
{
	public function run()
	{
		$config = config('AtomicAuth\\Config\\AtomicAuth');
		$this->DBGroup = empty($config->databaseGroupName) ? '' : $config->databaseGroupName;
		$tables        = $config->tables;

		$adminCapabilities = [
			[ 'name' => 'list_user', 'description' => 'Can list users', ],
			[ 'name' => 'create_user', 'description' => 'Can create user', ],
			[ 'name' => 'edit_user', 'description' => 'Can edit user', ],
			[ 'name' => 'delete_user', 'description' => 'Can delete user', ],
			[ 'name' => 'promote_user', 'description' => 'Can add/remove user to roles', ],
		];

		$defaultCapabilities = [
			[ 'name' => 'edit_self', 'description' => 'Allow user to edit themself', ],
		];

		if($this->db->tableExists($config->tables['capabilities']))
		{
			$this->db->table($config->tables['capabilities'])->insertBatch($adminCapabilities);
			$this->db->table($config->tables['capabilities'])->insertBatch($defaultCapabilities);
		}


		if($this->db->tableExists($config->tables['roles_capabilities'])  && !empty($config->adminRole) )
		{
			$roles = $this->db->table($config->tables['roles'])->select('id, name')->where('guid', $config->adminRole )->orWhere('guid', $config->defaultRole )->get()->getResult();
			foreach( $roles as $role )
			{
				$capabilitiesList = $role->name . 'Capabilities';
				$permNames = array_column($$capabilitiesList, 'name');
				$permIds = $this->db->table($config->tables['capabilities'])->select('id')->where( " name='" . implode( "' OR name='", $permNames) . "' ", NULL, FALSE )->get()->getResult();
				foreach( $permIds as $permId)
				{
					$roles_capabilities[] = [
						'role_id' => (string) $role->id,
						'capability_id' => (string) $permId->id ];
				}
			}

			$this->db->table($config->tables['roles_capabilities'])->insertBatch($roles_capabilities);
		}

	}
}
