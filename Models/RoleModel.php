<?php namespace AtomicAuth\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
  protected $table         = 'atomicauth_roles AS role'; // TODO make this dynamically driven via config
  protected $allowedFields = [
      'guid', 'name', 'description', 'status'
  ];
  protected $returnType    = 'AtomicAuth\Entities\Role';
  protected $useTimestamps = true;

  public function setDbTable( string $name = null )
  {
    // TODO be a good keeper and check if table exists first?
    $this->table = !is_null($name) ? $name : $this->table;
  }

  public function getGroupByGuid( string $guid = null )
  {
    if( empty ( $guid ) )
    {
      return null;
    }
    return $this->asObject()->where('guid', $guid)->limit(1)->first();
  }

  public function getRolesByUserId( ?int $userId = null )
  {
    /**
		 * This was pretty complex - saving the raw query for later debugging if needed
     *
		 * SELECT `role`.`id`, `role`.`guid`, `role`.`name`, `role`.`description`, `role`.`status`
		 * FROM `atomicauth_roles` AS `role`
		 * LEFT JOIN `atomicauth_roles_users` AS `role_usr` ON `role_usr`.`role_id` = `role`.`id`
		 * WHERE `role_usr`.`user_id` = 1
		 * AND `role`.`status` = 1
		 */
    $roleEntity = new $this->returnType; //\AtomicAuth\Entities\Role();
    $userRoles = $this->select('role.id,role.guid,role.name,role.description,role.status' )
      // TODO make roles_users extensible
      ->join('atomicauth_roles_users AS role_usr', 'role_usr.role_id = role.id', 'left')
      ->where('role_usr.user_id', $userId)
      ->where('role.status', $roleEntity->statusValueMap['active'])
      ->get()->getResult();
      return $userRoles;
  }

}
