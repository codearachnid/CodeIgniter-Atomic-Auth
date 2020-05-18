<?php
namespace AtomicAuth\Controllers;

/**
 * Class Auth
 *
 * @property Atomic_auth|Atomic_auth_model $atomic_auth      The ION Auth spark
 * @package  CodeIgniter-Atomic-Auth
 * @author   Ben Edmunds <ben.edmunds@gmail.com>
 * @author   Benoit VRIGNAUD <benoit.vrignaud@zaclys.net>
 * @license  https://opensource.org/licenses/MIT	MIT License
 */
class Admin extends \CodeIgniter\Controller
{

  	/**
  	 * AtomicAuth library
  	 *
  	 * @var \AtomicAuth\Libraries\AtomicAuth
  	 */
  	protected $atomicAuth;

  	/**
  	 * Migrations folder
  	 *
  	 * @var string
  	 */
    protected $pathMigrate = 'AtomicAuth\Database\Migrations';

  	/**
  	 * Constructor
  	 *
  	 * @return void
  	 */
  	public function __construct()
  	{
  		$this->atomicAuth    = new \AtomicAuth\Libraries\AtomicAuth();
  		$this->configAtomicAuth = config('AtomicAuth');
  	}

    public function salt( $length = 32 )
    {
      $bytes = openssl_random_pseudo_bytes($length);
      $hex   = bin2hex($bytes);

      return $hex;
    }

    public function go_away()
    {
      return redirect()->to('/');
    }

    public function install()
    {
      $migrate = \Config\Services::migrations();
      echo 'migrate';
      try
      {
        $migrate->setNamespace($this->pathMigrate)->latest();
        echo 'migrated';
      }
      catch (\Exception $e)
      {
        // Do something with the error here...
      }
    }

    public function uninstall()
    {

    }
}
