<?php namespace AtomicAuth\Controllers;

class Role extends \CodeIgniter\Controller
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
    protected $config;

    /**
     * AtomicAuth library
     *
     * @var \AtomicAuth\Libraries\AtomicAuth
     */
    protected $atomicAuth;

    /**
     * Session
     *
     * @var \CodeIgniter\Session\Session
     */
    protected $session;

    /**
     * Validation library
     *
     * @var \CodeIgniter\Validation\Validation
     */
    protected $validation;

    /**
     * Validation list template.
     *
     * @var string
     * @see https://bcit-ci.github.io/CodeIgniter4/libraries/validation.html#configuration
     */
    protected $validationListTemplate = 'list';

    /**
     * Views folder
     * Set it to 'auth' if your views files are in the standard application/Views/auth
     *
     * @var string
     */
    protected $pathViews = 'AtomicAuth\Views\auth';

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->atomicAuth    = new \AtomicAuth\Libraries\AtomicAuth();
        $this->validation = \Config\Services::validation();
        // helper(['form', 'url','AtomicAuth\auth']);
        $this->config = config('AtomicAuth');
        $this->session       = \Config\Services::session();

        if (! $this->atomicAuth->isLoggedIn()) {
            // redirect them to the login page
            return redirect()->to('/auth/login');
        }
    }

    public function list()
    {

    }
}
