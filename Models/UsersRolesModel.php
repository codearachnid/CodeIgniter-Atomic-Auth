<?php namespace AtomicAuth\Models;

use CodeIgniter\Model;

class UsersRolesModel extends Model
{
    protected $table         = 'atomicauth_roles_users'; // TODO make this dynamically driven via config
    protected $allowedFields = [
      'user_id', 'role_id',
    ];
    protected $returnType    = 'AtomicAuth\Entities\UsersRoles';
    protected $useTimestamps = false;


    /**
     * Add to role
     *
     * @param array|integer $roleIds Groups id
     * @param integer       $userId   User id
     *
     * @return integer The number of roles added
     * @author Ben Edmunds
     */
    public function setUserToRole(?array $roleIds = null, ?int $userId = null, bool $append = false): int
    {

        if (!$roleIds || !$userId) {
            return 0;
        }
        if (! is_array($roleIds)) {
            $roleIds = [$roleIds];
        }

        $rolesUsers = [];

        foreach ($roleIds as $role) {
            if (is_object($role) && ! is_null($role->id)) {
                // $role is an Group Entity
                $roleId = $role->id;
            } elseif (is_int($role) || is_float($role) || is_string($role)) {
                // $role is just a role id
                $roleId = $role;
            } else {
                // could not determine the type of data for $role silent ignore
                continue;
            }
            // Cast to float to support bigint data type
            $rolesUsers[] = [
                'role_id' => (float)$roleId, // assumed Group exists
                'user_id' => (float)$userId, // assumed User exists
            ];

            // TODO should this be cached?
        }

        if (! empty($rolesUsers)) {
            // configure the lookup for delete where clause
            $this->primaryKey = 'user_id';
            if (!$append && $this->delete(['user_id' => $userId])) {
                $this->insertBatch($rolesUsers);
            }
            else
            {
                $this->insertBatch($rolesUsers);
            }
        }

        return count($rolesUsers);
    }


    public function getRolesByUserId(?int $userId = null)
    {
        /**
         * This was pretty complex - saving the raw query for later debugging if needed
         *
         * SELECT `role`.`id`, `role`.`guid`, `role`.`name`, `role`.`description`, `role`.`status`
         * FROM `atomicauth_roles` AS `role`
         * LEFT JOIN `atomicauth_roles_users` AS `role_usr`
         *    ON `role_usr`.`role_id` = `role`.`id`
         * WHERE `role_usr`.`user_id` = 1
         * AND `role`.`status` = 1
         */
        // tightly coupled to the role entity
        $roleEntity = new \AtomicAuth\Entities\Role();
        $userRoles = $this->builder('atomicauth_roles AS role')->select('role.id,role.guid,role.name,role.description,role.status')
        ->join($this->table . ' AS role_usr', 'role_usr.role_id = role.id', 'left')
        ->where('role_usr.user_id', $userId)
        ->where('role.status', $roleEntity->statusValueMap['active'])
        ->get()->getResult();
        return $userRoles;
    }
}
