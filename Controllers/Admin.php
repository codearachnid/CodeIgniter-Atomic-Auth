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
    protected $runNamespace = 'AtomicAuth';

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
      // load up the default migration runner
      $migrate = \Config\Services::migrations();

      try
      {
        // echo $this->runNamespace . '<br />';
        // print_r($migrate->setNamespace($this->runNamespace)->findMigrations());
        // $migrate->setNamespace($this->runNamespace)->latest();
        // print_r($migrate->findNamespaceMigrations($this->runNamespace));
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
