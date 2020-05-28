<?php namespace AtomicAuth\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * CodeIgniter AtomicAuth
 *
 * @package CodeIgniter-Atomic-Auth
 * @author  Timothy Wood @codearachnid <codearachnid@gmail.com>
 * @license https://opensource.org/licenses/GPL-3.0	GNU General Public License v3.0
 * @link    https://github.com/codearachnid/CodeIgniter-Atomic-Auth
 */

/**
 * Migration class
 *
 * @package CodeIgniter-Atomic-Auth
 */
class InstallAtomicAuth extends Migration
{
    /**
     * Tables
     *
     * @var array
     */
    private $tables;

    /**
     * Construct
     *
     * @return void
     */
    public function __construct()
    {
        $config = config('AtomicAuth');

        // initialize the database
        $this->DBGroup = empty($config->databaseGroupName) ? '' : $config->databaseGroupName;

        parent::__construct();

        // import the custom table names
        $this->tables = $config->tables;
    }

    /**
     * Up
     *
     * @return void
     */
    public function up()
    {

        /*
     * Configuration
     */
        // TODO create a database driven configuration?
        // $this->forge->addField([
        //       'id'               => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
        //			 'guid'        			=> ['type' => 'varchar', 'constraint' => 32, 'null' => false],
        //       'option_name'      => ['type' => 'varchar', 'constraint' => 64],
        //       'option_value'     => ['type' => 'longtext', 'null' => true],
        //       'created_at'       => ['type' => 'datetime', 'null' => true],
        //       'updated_at'       => ['type' => 'datetime', 'null' => true],
        //   ]);
        //   $this->forge->addKey('id', true);
        //   $this->forge->createTable($this->tables['config'], true);


        /*
     * Users
     */
        $this->forge->addField([
            'id'               => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                    'guid'        		 => ['type' => 'varchar', 'constraint' => 32, 'null' => false],
            'email'            => ['type' => 'varchar', 'constraint' => 255],
            'password_hash'    => ['type' => 'varchar', 'constraint' => 255],
            'reset_hash'       => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'reset_at'         => ['type' => 'datetime', 'null' => true],
            'reset_expires'    => ['type' => 'datetime', 'null' => true],
            'activate_hash'    => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'status'           => ['type' => 'varchar', 'constraint' => 15, 'null' => 0, 'default' => 'disabled'],
            'status_message'   => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'force_pass_reset' => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            'created_at'       => ['type' => 'datetime', 'null' => true],
            'updated_at'       => ['type' => 'datetime', 'null' => true],
            'deleted_at'       => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->addUniqueKey('guid');
        $this->forge->createTable($this->tables['users'], true);


        /*
         * Roles Table
         */
        $fields = [
                      'id'          => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                                'guid'        => ['type' => 'varchar', 'constraint' => 32, 'null' => false],
                      'name'        => ['type' => 'varchar', 'constraint' => 255],
                      'description' => ['type' => 'varchar', 'constraint' => 255],
                                'status'           => ['type' => 'tinyint', 'constraint' => 1, 'null' => false, 'default' => 0],
                                'created_at'       => ['type' => 'datetime', 'null' => true],
                      'updated_at'       => ['type' => 'datetime', 'null' => true],
                      'deleted_at'       => ['type' => 'datetime', 'null' => true],
                  ];

        $this->forge->addField($fields);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('guid');
        $this->forge->createTable($this->tables['roles'], true);

        /*
         * Capabilities Table
         */
        $fields = [
                      'id'          => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                      'name'        => ['type' => 'varchar', 'constraint' => 255, 'null' => false],
                      'description' => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
                                'status'           => ['type' => 'tinyint', 'constraint' => 1, 'null' => false, 'default' => 0],
                                'created_at'       => ['type' => 'datetime', 'null' => true],
                      'updated_at'       => ['type' => 'datetime', 'null' => true],
                      'deleted_at'       => ['type' => 'datetime', 'null' => true],
                  ];

        $this->forge->addField($fields);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('name');
        $this->forge->createTable($this->tables['capabilities'], true);


        /*
       * Track Logins
       */
        $this->forge->addField([
          'id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
          'ip_address' => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
          'identity'      => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
          'user_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true], // Only for successful logins
          'activity'    => ['type' => 'varchar', 'constraint' => 15],
                    'created_at'       => ['type' => 'datetime', 'null' => true],
                    'deleted_at'       => ['type' => 'datetime', 'null' => true],
      ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('email');
        $this->forge->addKey('user_id');
        // NOTE: Do NOT delete the user_id or email when the user is deleted for security audits
        $this->forge->createTable($this->tables['track_login'], true);

        /*
         * Auth Tokens
         * @see https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
         */
        $this->forge->addField([
          'id'              => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
          'selector'        => ['type' => 'varchar', 'constraint' => 255],
          'hashedValidator' => ['type' => 'varchar', 'constraint' => 255],
          'user_id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
                    'created_at'       => ['type' => 'datetime', 'null' => true],
                    'expires_at'			=> ['type' => 'datetime'],
      ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('selector');
        $this->forge->addForeignKey('user_id', $this->tables['users'], 'id', false, 'CASCADE');
        $this->forge->createTable($this->tables['tokens'], true);

        /*
         * Password Reset Table
         */
        $this->forge->addField([
          'id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
          'email'      => ['type' => 'varchar', 'constraint' => 255],
          'ip_address' => ['type' => 'varchar', 'constraint' => 255],
          'user_agent' => ['type' => 'varchar', 'constraint' => 255],
          'token'      => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
          'created_at' => ['type' => 'datetime', 'null' => true],
                    'expires_at' => ['type' => 'datetime', 'null' => true],
      ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable($this->tables['reset_attempts'], true);

        /*
         * Activation Attempts Table
         */
        // $this->forge->addField([
        //     'id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
        //     'ip_address' => ['type' => 'varchar', 'constraint' => 255],
        //     'user_agent' => ['type' => 'varchar', 'constraint' => 255],
        //     'token'      => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
        //     'created_at' => ['type' => 'datetime', 'null' => false],
        // ]);
        // $this->forge->addKey('id', true);
        // $this->forge->createTable($this->tables['activation_attempts']);

        /*
         * Roles/Capabilities Table
         */
        $fields = [
          'role_id'      => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
          'capability_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
      ];

        $this->forge->addField($fields);
        $this->forge->addKey(['role_id', 'capability_id']);
        $this->forge->addForeignKey('role_id', $this->tables['roles'], 'id', false, 'CASCADE');
        $this->forge->addForeignKey('capability_id', $this->tables['capabilities'], 'id', false, 'CASCADE');
        $this->forge->createTable($this->tables['roles_capabilities'], true);

        /*
         * Users/Roles Table
         */
        $fields = [
          'role_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
          'user_id'  => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
      ];

        $this->forge->addField($fields);
        $this->forge->addKey(['role_id', 'user_id']);
        $this->forge->addForeignKey('role_id', $this->tables['roles'], 'id', false, 'CASCADE');
        $this->forge->addForeignKey('user_id', $this->tables['users'], 'id', false, 'CASCADE');
        $this->forge->createTable($this->tables['roles_users'], true);

        /*
         * Users/Capabilities Table
         */
        $fields = [
          'user_id'       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
          'capability_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
      ];

        $this->forge->addField($fields);
        $this->forge->addKey(['user_id', 'capability_id']);
        $this->forge->addForeignKey('user_id', $this->tables['users'], 'id', false, 'CASCADE');
        $this->forge->addForeignKey('capability_id', $this->tables['capabilities'], 'id', false, 'CASCADE');
        $this->forge->createTable($this->tables['users_capabilities'], true);
    }

    /**
     * Down
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->tables as $table) {
            $this->forge->dropTable($table, true);
        }
    }
}
