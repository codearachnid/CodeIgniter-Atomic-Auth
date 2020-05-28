<?php namespace AtomicAuth\Database\Seeds;

/**
 * @package CodeIgniter-Atomic-Auth
 */

class AtomicAuthRolesSeeder extends \CodeIgniter\Database\Seeder
{
    public function run()
    {
        $config = config('AtomicAuth\\Config\\AtomicAuth');
        $this->DBGroup = empty($config->databaseGroupName) ? '' : $config->databaseGroupName;

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
        $this->db->table($config->tables['users'])->insertBatch($users);

        $usersRoles = [
            [
                'user_id'  => '1',
                'role_id' => '1',
            ],
            [
                'user_id'  => '1',
                'role_id' => '2',
            ],
        ];
        $this->db->table($config->tables['users_roles'])->insertBatch($usersRoles);
    }
}
