<?php namespace AtomicAuth\Controllers;

/**
 * Class Api
 *
 * This package can stand alone with the included UI, integrated views, or
 * interface through the underlying API.
 *
 * @package  CodeIgniter-Atomic-Auth
 * @author   Timothy Wood <codearachnid@gmail.com>
 */
class Api extends \CodeIgniter\Controller
{

  /**
   *
   * @var array
   */
  public $data = [];

  /**
   * Configuration
   *
   * @var \AtomicAuth\Config\AtomicAuth
   */
  protected $configAtomicAuth;

  /**
   * AtomicAuth library
   *
   * @var \AtomicAuth\Libraries\AtomicAuth
   */
  protected $atomicAuth;

  /**
   * Constructor
   *
   * @return void
   */
  public function __construct()
  {
      $this->atomicAuth    = new \AtomicAuth\Libraries\AtomicAuth();
      // $this->validation = \Config\Services::validation();
      $this->configAtomicAuth = config('AtomicAuth');
  }

}
