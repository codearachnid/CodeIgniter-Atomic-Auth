<?php namespace AtomicAuth\Models;

use CodeIgniter\Model;

class CapabilityModel extends Model
{
  protected $table         = 'atomicauth_capabilities as cap'; // TODO make this dynamically driven via config
  protected $allowedFields = [
      'guid', 'name', 'description'
  ];
  protected $returnType    = 'AtomicAuth\Entities\Role';
  protected $useTimestamps = true;

  public function getPermissionByName( string $name = null )
  {
    if( empty ( $name ) )
    {
      return null;
    }
    return $this->asObject()->where('name', $name)->limit(1)->first();
  }

  /**
	 * Get User capabilities (includes capabilities tied to role user is in)
	 */
   // TODO query performance to limit joins to user only via $userOnly
	public function getCapabilitiesByUser( int $userId = null, bool $userOnly = false  )
	{
		if( is_null($userId) )
		{
			return null;
		}
		/**
		 * This was pretty complex - saving the raw query for later debugging if needed
     *
		 * SELECT `cap`.`id`, `cap`.`name`
		 * FROM `atomicauth_capabilities` as `cap`
		 * LEFT JOIN `atomicauth_roles_capabilities` AS `role_cap` ON `role_cap`.`capability_id` = `cap`.`id`
		 * LEFT JOIN `atomicauth_roles` AS `role` ON `role`.`id` = `role_cap`.`role_id`
		 * LEFT JOIN `atomicauth_roles_users` AS `role_usr` ON `role_usr`.`role_id` = `role`.`id`
		 * LEFT JOIN `atomicauth_users_capabilities` AS `usr_cap` ON `usr_cap`.`capability_id` = `cap`.`id`
		 * WHERE `role_usr`.`user_id` = 1
		 * OR `usr_cap`.`user_id` = 1
		 */
		$capabilities = $this->select('cap.id,cap.name')
			->join('atomicauth_roles_capabilities AS role_cap', 'role_cap.capability_id = cap.id', 'left')
			->join('atomicauth_roles AS role', 'role.id = role_cap.role_id', 'left')
			->join('atomicauth_roles_users AS role_usr', 'role_usr.role_id = role.id', 'left')
			->join('atomicauth_users_capabilities AS usr_cap', 'usr_cap.capability_id = cap.id', 'left')
			->where('role_usr.user_id', $userId)
			->orWhere('usr_cap.user_id', $userId)
			->get()->getResult();
		return $capabilities;
	}

}
