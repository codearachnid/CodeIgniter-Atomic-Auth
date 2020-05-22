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

		$permissions = [
			[ 'name' => 'list_user', 'description' => 'Can list users', ],
			[ 'name' => 'create_user', 'description' => 'Can create user', ],
			[ 'name' => 'edit_user', 'description' => 'Can edit user', ],
			[ 'name' => 'delete_user', 'description' => 'Can delete user', ],
			[ 'name' => 'promote_user', 'description' => 'Can add/remove user to groups', ],
		];

		if($this->db->tableExists($config->tables['permissions']))
		{
			$this->db->table($config->tables['permissions'])->insertBatch($permissions);
		}


		if($this->db->tableExists($config->tables['groups_permissions'])  && !empty($config->adminGroup) )
		{
			$permKeys = array_column($permissions, 'name');
			$permIds = $this->db->table($config->tables['permissions'])->select('id')->where( "name='" . implode( "' OR name='", $permKeys) . "'", NULL, FALSE )->get()->getResultArray();
			$adminGroup = $this->db->table($config->tables['groups'])->where('guid', $config->adminGroup )->get()->getRow();
			foreach( $permIds as $permId)
			{
				$groups_permissions[] = [ 'group_id' => $adminGroup->id, 'permission_id' => $permId ];
			}

			$this->db->table($config->tables['groups_permissions'])->insertBatch($groups_permissions);
		}

	}
}