<?php namespace AtomicAuth\Controllers;

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
  	 * Configuration
  	 *
  	 * @var \AtomicAuth\Config\AtomicAuth
  	 */
  	protected $configAtomicAuth;

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

    /**
    *
    */
    public function go_away()
    {
      // TODO clear all the things before kick them to the base
      return redirect()->to('/');
    }

    public function install()
    {

      // ensure we can proceed
      if( ! $this->has_access() )
      {
        return $this->go_away();
      }

      // load up the MigrationRunner
      $migrate = \Config\Services::migrations();

      try
      {
        $migrate->setNamespace($this->runNamespace)->latest();
      }
      catch (\Exception $e)
      {
        // Do something with the error here...
      }

      return view('welcome_message');
    }

    public function uninstall()
    {
      // ensure we can proceed
      if( ! $this->has_access() )
      {
        return $this->go_away();
      }

      // load up the MigrationRunner
      $migrate = \Config\Services::migrations();

      try
      {
        $migrate->setNamespace($this->runNamespace)->regress();
      }
      catch (\Exception $e)
      {
        // Do something with the error here...
      }

      return view('welcome_message');
    }

    private function has_access()
    {
      $access_check = false;

      // compare url hash to config hash
      if( ! empty( $this->request->uri->getQuery(['only' => [ $this->configAtomicAuth->accessHash ]]) ) )
      {
        $access_check = true;
      }

      return $access_check;
    }
}
