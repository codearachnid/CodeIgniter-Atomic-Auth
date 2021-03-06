<?php
namespace AtomicAuth\Controllers;

/**
 * Class Auth
 *
 * @property Atomic_auth|Atomic_auth_model $atomic_auth
 * @package  CodeIgniter-Atomic-Auth
 * @author   Timothy Wood <codearachnid@gmail.com>
 * @author   Ben Edmunds <ben.edmunds@gmail.com>
 * @author   Benoit VRIGNAUD <benoit.vrignaud@zaclys.net>
 * @license  https://opensource.org/licenses/MIT	MIT License
 */
class Auth extends \CodeIgniter\Controller
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
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->atomicAuth    = new \AtomicAuth\Libraries\AtomicAuth();
        $this->validation = \Config\Services::validation();
        helper(['form', 'url', 'AtomicAuth\auth',]);
        $this->configAtomicAuth = config('AtomicAuth');
        $this->session       = \Config\Services::session();
    }

    /**
     * Redirect if needed, otherwise display the user list
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function index()
    {
        if (! $this->atomicAuth->isLoggedIn()) {
            // redirect them to the login page
            return redirect()->to('/auth/login');
        } elseif (! $this->atomicAuth->isUserAdmin()) { // remove this elseif if you want to enable this for non-admins
            // redirect them to the home page because they must be an administrator to view this
            //show_error('You must be an administrator to view this page.');
            throw new \Exception('You must be an administrator to view this page.');
        } else {
            $this->data['title'] = lang('Auth.index_heading');

            // set the flash data error message if there is one
            $this->data['messages'] = []; //$this->validation->getErrors() ? $this->validation->listErrors() : $this->session->getflashdata('AtomicAuthMessages');
            //list the users
            if ($this->atomicAuth->userCan('list_user')) {
                $this->data['users'] = $this->atomicAuth->users()->result();
            }
            dd($this->data['users']);
            $this->data['atomicAuth'] = $this->atomicAuth;
            return view('AtomicAuth\Views\Auth\index', $this->data);
        }
    }

    /**
     * Log the user in
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function login()
    {
        $this->data['title'] = lang('Auth.login_heading');
        $this->data['messages'] = $this->atomicAuth->message()->get();

        // validate form input
        $this->validation->setRule('identity', str_replace(':', '', lang('Auth.login_identity_label')), 'required');
        $this->validation->setRule('password', str_replace(':', '', lang('Auth.login_password_label')), 'required');

        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
            // check to see if the user is logging in
            // check for "remember me"
            $remember = (bool)$this->request->getVar('remember');

            if ($this->atomicAuth->login($this->request->getVar('identity'), $this->request->getVar('password'), $remember)) {
                // if the login is successful
                // redirect them back to the home page
                // TODO better handling of redirect (if useful)
                return redirect()->to('/auth/user');
            } else {
                // if the login was un-successful
                // redirect them back to the login page
                // use redirects instead of loading views for compatibility with MY_Controller libraries
                return redirect()->back()->withInput();
            }
        }
        else if ($this->validation->getErrors())
        {


            // the user is not logging in so display the login page
            // set the flash data error message if there is one
            $this->data['messages'] = $this->validation->getErrors();
        }
        else if($this->atomicAuth->getErrors())
        {
          $this->data['messages'] = $this->atomicAuth->getErrors();
        }

        $this->data['identity'] = [
            'name'  => 'identity',
            'id'    => 'identity',
            'type'  => 'text',
            'value' => set_value('identity'),
        ];

        $this->data['password'] = [
            'name' => 'password',
            'id'   => 'password',
            'type' => 'password',
        ];

        return view('AtomicAuth\Views\login', $this->data);
    }

    /**
     * Log the user out
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function logout()
    {
        $this->atomicAuth->logout();
        return redirect()->to('/auth/login');
    }

    /**
     * Change password
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function change_password()
    {
        $this->validation->setRule('old', lang('Auth.change_password_validation_old_password_label'), 'required');
        $this->validation->setRule('new', lang('Auth.change_password_validation_new_password_label'), 'required|min_length[' . $this->configAtomicAuth->minPasswordLength . ']|matches[new_confirm]');
        $this->validation->setRule('new_confirm', lang('Auth.change_password_validation_new_password_confirm_label'), 'required');

        if (! $this->atomicAuth->isLoggedIn()) {
            return redirect()->to('/auth/login');
        }

        $user = $this->atomicAuth->user()->row();

        if ($this->validation->run() === false) {
            // display the form
            // set the flash data error message if there is one
            $this->data['messages'] = ($this->validation->getErrors()) ? $this->validation->listErrors() : $this->session->getflashdata('AtomicAuthMessages');

            $this->data['minPasswordLength'] = $this->configAtomicAuth->minPasswordLength;
            $this->data['old_password'] = [
                'name' => 'old',
                'id'   => 'old',
                'type' => 'password',
            ];
            $this->data['new_password'] = [
                'name'    => 'new',
                'id'      => 'new',
                'type'    => 'password',
                'pattern' => '^.{' . $this->data['minPasswordLength'] . '}.*$',
            ];
            $this->data['new_password_confirm'] = [
                'name'    => 'new_confirm',
                'id'      => 'new_confirm',
                'type'    => 'password',
                'pattern' => '^.{' . $this->data['minPasswordLength'] . '}.*$',
            ];
            $this->data['user_id'] = [
                'name'  => 'user_id',
                'id'    => 'user_id',
                'type'  => 'hidden',
                'value' => $user->id,
            ];

            // render
            return $this->renderPage($this->pathViews . DIRECTORY_SEPARATOR . 'change_password', $this->data);
        } else {
            $identity = $this->session->get('identity');

            $change = $this->atomicAuth->changePassword($identity, $this->request->getPost('old'), $this->request->getPost('new'));

            if ($change) {
                //if the password was successfully changed
                $this->session->setflashdata('AtomicAuthMessages', $this->atomicAuth->messages());
                $this->logout();
            } else {
                $this->session->setflashdata('AtomicAuthMessages', $this->atomicAuth->errors($this->validationListTemplate));
                return redirect()->to('/auth/change_password');
            }
        }
    }

    /**
     * Forgot password
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function forgot_password()
    {
        $this->data['title'] = lang('Auth.forgot_password_heading');

        // setting validation rules by checking whether identity is username or email
        if ($this->configAtomicAuth->identity == 'email')
        {
            $this->validation->setRule('identity', lang('Auth.forgot_password_validation_email_label'), 'required|valid_email');
            $this->data['identity_label'] = lang('Auth.forgot_password_email_identity_label');
        }
        else
        {
            $this->validation->setRule('identity', lang('Auth.forgot_password_identity_label'), 'required');
            $this->data['identity_label'] = lang('Auth.forgot_password_identity_label');
        }



        if (! ($this->request->getPost() && $this->validation->withRequest($this->request)->run()))
        {
            $this->data['type'] = $this->configAtomicAuth->identity;
            // setup the input
            $this->data['identity'] = [
                'name' => 'identity',
                'id'   => 'identity',
            ];

            // set any errors and display the form
            $this->data['messages'] = $this->validation->getErrors();
        }
        else
        {

            if ( $this->atomicAuth->user()->identityExists( $this->request->getPost('identity') ) )
            {
              // run the forgotten password method to email an activation code to the user
                if ($this->atomicAuth->emailForgottenPasswordRequest($this->request->getPost('identity')))
                {
                    // if there were no errors
                    $this->atomicAuth->message()->set('Auth.forgot_password_email_sent', 'info');
                    return redirect()->to('/auth/login');
                }
                else
                {
                    $this->atomicAuth->message()->set('Auth.forgot_password_email_not_sent', 'error');
                }
            }
            else if ($this->configAtomicAuth->identity == 'email')
            {
              $this->atomicAuth->message()->set('Auth.forgot_password_email_not_found', 'error');
              }
              else
              {
                  $this->atomicAuth->message()->set('Auth.forgot_password_identity_not_found', 'error');
            }

        }

        return view('AtomicAuth\Views\forgot_password', $this->data);
    }

    /**
     * Reset password - final step for forgotten password
     *
     * @param string|null $code The reset code
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function reset_password($code = null)
    {
        if (! $code) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->data['title'] = lang('Auth.reset_password_heading');

        $user = $this->atomicAuth->forgottenPasswordCheck($code);

        if ($user) {
            // if the code is valid then display the password reset form

            $this->validation->setRule('new', lang('Auth.reset_password_validation_new_password_label'), 'required|min_length[' . $this->configAtomicAuth->minPasswordLength . ']|matches[new_confirm]');
            $this->validation->setRule('new_confirm', lang('Auth.reset_password_validation_new_password_confirm_label'), 'required');

            if (! $this->request->getPost() || $this->validation->withRequest($this->request)->run() === false) {
                // display the form

                // set the flash data error message if there is one
                $this->data['messages'] = $this->validation->getErrors() ? $this->validation->listErrors() : $this->session->getflashdata('AtomicAuthMessages');

                $this->data['minPasswordLength'] = $this->configAtomicAuth->minPasswordLength;
                $this->data['new_password'] = [
                    'name'    => 'new',
                    'id'      => 'new',
                    'type'    => 'password',
                    'pattern' => '^.{' . $this->data['minPasswordLength'] . '}.*$',
                ];
                $this->data['new_password_confirm'] = [
                    'name'    => 'new_confirm',
                    'id'      => 'new_confirm',
                    'type'    => 'password',
                    'pattern' => '^.{' . $this->data['minPasswordLength'] . '}.*$',
                ];
                $this->data['user_id'] = [
                    'name'  => 'user_id',
                    'id'    => 'user_id',
                    'type'  => 'hidden',
                    'value' => $user->id,
                ];
                $this->data['code'] = $code;

                // render
                return $this->renderPage($this->pathViews . DIRECTORY_SEPARATOR . 'reset_password', $this->data);
            } else {
                $identity = $user->{$this->configAtomicAuth->identity};

                // do we have a valid request?
                if ($user->id != $this->request->getPost('user_id')) {
                    // something fishy might be up
                    $this->atomicAuth->clearForgottenPasswordCode($identity);

                    throw new \Exception(lang('Auth.error_security'));
                } else {
                    // finally change the password
                    $change = $this->atomicAuth->resetPassword($identity, $this->request->getPost('new'));

                    if ($change) {
                        // if the password was successfully changed
                        $this->session->setflashdata('AtomicAuthMessages', $this->atomicAuth->messages());
                        return redirect()->to('/auth/login');
                    } else {
                        $this->session->setflashdata('AtomicAuthMessages', $this->atomicAuth->errors($this->validationListTemplate));
                        return redirect()->to('/auth/reset_password/' . $code);
                    }
                }
            }
        } else {
            // if the code is invalid then send them back to the forgot password page
            $this->session->setflashdata('AtomicAuthMessages', $this->atomicAuth->errors($this->validationListTemplate));
            return redirect()->to('/auth/forgot_password');
        }
    }

    /**
     * Activate the user
     *
     * @param integer $id   The user ID
     * @param string  $code The activation code
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function activate(int $id, string $code = ''): \CodeIgniter\HTTP\RedirectResponse
    {
        $activation = false;

        if ($code) {
            $activation = $this->atomicAuth->activate($id, $code);
        } elseif ($this->atomicAuth->isUserAdmin()) {
            $activation = $this->atomicAuth->activate($id);
        }

        if ($activation) {
            // redirect them to the auth page
            $this->session->setflashdata('AtomicAuthMessages', $this->atomicAuth->messages());
            return redirect()->to('/auth');
        } else {
            // redirect them to the forgot password page
            $this->session->setflashdata('AtomicAuthMessages', $this->atomicAuth->errors($this->validationListTemplate));
            return redirect()->to('/auth/forgot_password');
        }
    }

    /**
     * Deactivate the user
     *
     * @param integer $id The user ID
     *
     * @throw Exception
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function deactivate(int $id = 0)
    {
        if (! $this->atomicAuth->isLoggedIn() || ! $this->atomicAuth->isUserAdmin()) {
            // redirect them to the home page because they must be an administrator to view this
            throw new \Exception('You must be an administrator to view this page.');
            // TODO : I think it could be nice to have a dedicated exception like '\AtomicAuth\Exception\NotAllowed
        }

        $this->validation->setRule('confirm', lang('Auth.deactivate_validation_confirm_label'), 'required');
        $this->validation->setRule('id', lang('Auth.deactivate_validation_user_id_label'), 'required|integer');

        if (! $this->validation->withRequest($this->request)->run()) {
            $this->data['user'] = $this->atomicAuth->user($id)->row();
            return $this->renderPage($this->pathViews . DIRECTORY_SEPARATOR . 'deactivate_user', $this->data);
        } else {
            // do we really want to deactivate?
            if ($this->request->getPost('confirm') === 'yes') {
                // do we have a valid request?
                if ($id !== $this->request->getPost('id', FILTER_VALIDATE_INT)) {
                    throw new \Exception(lang('Auth.error_security'));
                }

                // do we have the right userlevel?
                if ($this->atomicAuth->isLoggedIn() && $this->atomicAuth->isUserAdmin()) {
                    $message = $this->atomicAuth->deactivate($id) ? $this->atomicAuth->messages() : $this->atomicAuth->errors($this->validationListTemplate);
                    $this->session->setflashdata('AtomicAuthMessages', $message);
                }
            }

            // redirect them back to the auth page
            return redirect()->to('/auth');
        }
    }

    /**
     * Redirect a user checking if is admin
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function goToMain()
    {
        if ($this->atomicAuth->isUserAdmin()) {
            return redirect()->to('/auth');
        }
        return redirect()->to('/');
    }

    /**
     * Create a new role
     *
     * @return string string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function create_role()
    {
        $this->data['title'] = lang('Auth.create_role_title');

        if (! $this->atomicAuth->isLoggedIn() || ! $this->atomicAuth->isUserAdmin()) {
            return redirect()->to('/auth');
        }

        // validate form input
        $this->validation->setRule('role_name', lang('Auth.create_role_validation_name_label'), 'trim|required|alpha_dash');

        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
            $newGroupId = $this->atomicAuth->createGroup($this->request->getPost('role_name'), $this->request->getPost('description'));
            if ($newGroupId) {
                // check to see if we are creating the role
                // redirect them back to the admin page
                $this->session->setflashdata('AtomicAuthMessages', $this->atomicAuth->messages());
                return redirect()->to('/auth');
            }
        } else {
            // display the create role form
            // set the flash data error message if there is one
            $this->data['messages'] = $this->validation->getErrors() ? $this->validation->listErrors() : ($this->atomicAuth->errors($this->validationListTemplate) ? $this->atomicAuth->errors($this->validationListTemplate) : $this->session->getflashdata('AtomicAuthMessages'));

            $this->data['role_name'] = [
                'name'  => 'role_name',
                'id'    => 'role_name',
                'type'  => 'text',
                'value' => set_value('role_name'),
            ];
            $this->data['description'] = [
                'name'  => 'description',
                'id'    => 'description',
                'type'  => 'text',
                'value' => set_value('description'),
            ];

            return $this->renderPage($this->pathViews . DIRECTORY_SEPARATOR . 'create_role', $this->data);
        }
    }

    /**
     * Edit a role
     *
     * @param integer $id Group id
     *
     * @return string|CodeIgniter\Http\Response
     */
    public function edit_role(int $id = 0)
    {
        // bail if no role id given
        if (! $id) {
            return redirect()->to('/auth');
        }

        $this->data['title'] = lang('Auth.edit_role_title');

        if (! $this->atomicAuth->isLoggedIn() || ! $this->atomicAuth->isUserAdmin()) {
            return redirect()->to('/auth');
        }

        $role = $this->atomicAuth->role($id)->row();

        // validate form input
        $this->validation->setRule('role_name', lang('Auth.edit_role_validation_name_label'), 'required|alpha_dash');

        if ($this->request->getPost()) {
            if ($this->validation->withRequest($this->request)->run()) {
                $roleUpdate = $this->atomicAuth->updateGroup($id, $this->request->getPost('role_name'), ['description' => $this->request->getPost('role_description')]);

                if ($roleUpdate) {
                    $this->session->setflashdata('AtomicAuthMessages', lang('Auth.edit_role_saved'));
                } else {
                    $this->session->setflashdata('AtomicAuthMessages', $this->atomicAuth->errors($this->validationListTemplate));
                }
                return redirect()->to('/auth');
            }
        }

        // set the flash data error message if there is one
        $this->data['messages'] = $this->validation->listErrors() ?: ($this->atomicAuth->errors($this->validationListTemplate) ?: $this->session->getflashdata('AtomicAuthMessages'));

        // pass the user to the view
        $this->data['role'] = $role;

        $readonly = $this->configAtomicAuth->adminRole === $role->name ? 'readonly' : '';

        $this->data['role_name']        = [
            'name'    => 'role_name',
            'id'      => 'role_name',
            'type'    => 'text',
            'value'   => set_value('role_name', $role->name),
            $readonly => $readonly,
        ];
        $this->data['role_description'] = [
            'name'  => 'role_description',
            'id'    => 'role_description',
            'type'  => 'text',
            'value' => set_value('role_description', $role->description),
        ];

        return $this->renderPage($this->pathViews . DIRECTORY_SEPARATOR . 'edit_role', $this->data);
    }
}
