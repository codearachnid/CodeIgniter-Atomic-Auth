<?php namespace AtomicAuth\Database\Seeds;

/**
 * @package CodeIgniter-Atomic-Auth
 */

class AtomicAuthPermissions extends \CodeIgniter\Database\Seeder
{
	public function run()
	{
		$config = config('AtomicAuth\\Config\\AtomicAuth');
		$this->DBGroup = empty($config->databaseGroupName) ? '' : $config->databaseGroupName;
		$tables        = $config->tables;

		$adminPermissions = [
			[ 'name' => 'list_user', 'description' => 'Can list users', ],
			[ 'name' => 'create_user', 'description' => 'Can create user', ],
			[ 'name' => 'edit_user', 'description' => 'Can edit user', ],
			[ 'name' => 'delete_user', 'description' => 'Can delete user', ],
			[ 'name' => 'promote_user', 'description' => 'Can add/remove user to groups', ],
		];

		$memberPermissions = [
			[ 'name' => 'edit_self', 'description' => 'Allow user to edit themself', ],
		];

		if($this->db->tableExists($config->tables['permissions']))
		{
			$this->db->table($config->tables['permissions'])->insertBatch($adminPermissions);
			$this->db->table($config->tables['permissions'])->insertBatch($memberPermissions);
		}


		if($this->db->tableExists($config->tables['groups_permissions'])  && !empty($config->adminGroup) )
		{
			$groups = $this->db->table($config->tables['groups'])->select('id, name')->where('guid', $config->adminGroup )->orWhere('guid', $config->defaultGroup )->get()->getResult();
			foreach( $groups as $group )
			{
				$permissionsList = $group->name . 'Permissions';
				$permNames = array_column($$permissionsList, 'name');
				$permIds = $this->db->table($config->tables['permissions'])->select('id')->where( " name='" . implode( "' OR name='", $permNames) . "' ", NULL, FALSE )->get()->getResult();
				foreach( $permIds as $permId)
				{
					$groups_permissions[] = [
						'group_id' => (string) $group->id,
						'permission_id' => (string) $permId->id ];
				}
			}

			$this->db->table($config->tables['groups_permissions'])->insertBatch($groups_permissions);
		}

	}
}
