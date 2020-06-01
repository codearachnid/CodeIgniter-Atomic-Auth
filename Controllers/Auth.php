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
        helper(['form', 'url']);
        $this->configAtomicAuth = config('AtomicAuth');
        $this->session       = \Config\Services::session();

        if (! empty($this->configAtomicAuth->templates['errors']['list'])) {
            $this->validationListTemplate = $this->configAtomicAuth->templates['errors']['list'];
        }
    }

    /**
     * Redirect if needed, otherwise display the user list
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function index()
    {
        if (! $this->atomicAuth->loggedIn()) {
            // redirect them to the login page
            return redirect()->to('/auth/login');
        } elseif (! $this->atomicAuth->isAdmin()) { // remove this elseif if you want to enable this for non-admins
            // redirect them to the home page because they must be an administrator to view this
            //show_error('You must be an administrator to view this page.');
            throw new \Exception('You must be an administrator to view this page.');
        } else {
            $this->data['title'] = lang('Auth.index_heading');

            // set the flash data error message if there is one
            $this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
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
        $this->data['message'] = $this->session->getFlashdata('message');

        // validate form input
        $this->validation->setRule('identity', str_replace(':', '', lang('Auth.login_identity_label')), 'required');
        $this->validation->setRule('password', str_replace(':', '', lang('Auth.login_password_label')), 'required');

        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
            // check to see if the user is logging in
            // check for "remember me"
            $remember = (bool)$this->request->getVar('remember');

            if ($this->atomicAuth->login($this->request->getVar('identity'), $this->request->getVar('password'), $remember)) {
                //if the login is successful
                //redirect them back to the home page
                $this->session->setFlashdata('message', $this->atomicAuth->messages());
                // TODO better handling of redirect (if useful)
                return redirect()->to('/auth/user');
            } else {
                // if the login was un-successful
                // redirect them back to the login page
                $this->session->setFlashdata('message', $this->atomicAuth->errors($this->validationListTemplate));
                // use redirects instead of loading views for compatibility with MY_Controller libraries
                return redirect()->back()->withInput();
            }
        } else {
            // the user is not logging in so display the login page
            // set the flash data error message if there is one
            $this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->data['message'];
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

        return view('AtomicAuth\Views\Auth\login', $this->data);
    }

    /**
     * Log the user out
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function logout()
    {
        // log the user out
        $this->atomicAuth->logout();

        // redirect them to the login page
        $this->session->setFlashdata('message', $this->atomicAuth->messages());
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

        if (! $this->atomicAuth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $user = $this->atomicAuth->user()->row();

        if ($this->validation->run() === false) {
            // display the form
            // set the flash data error message if there is one
            $this->data['message'] = ($this->validation->getErrors()) ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

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
                $this->session->setFlashdata('message', $this->atomicAuth->messages());
                $this->logout();
            } else {
                $this->session->setFlashdata('message', $this->atomicAuth->errors($this->validationListTemplate));
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
        if ($this->configAtomicAuth->identity !== 'email') {
            $this->validation->setRule('identity', lang('Auth.forgot_password_identity_label'), 'required');
        } else {
            $this->validation->setRule('identity', lang('Auth.forgot_password_validation_email_label'), 'required|valid_email');
        }

        if (! ($this->request->getPost() && $this->validation->withRequest($this->request)->run())) {
            $this->data['type'] = $this->configAtomicAuth->identity;
            // setup the input
            $this->data['identity'] = [
                'name' => 'identity',
                'id'   => 'identity',
            ];

            if ($this->configAtomicAuth->identity !== 'email') {
                $this->data['identity_label'] = lang('Auth.forgot_password_identity_label');
            } else {
                $this->data['identity_label'] = lang('Auth.forgot_password_email_identity_label');
            }

            // set any errors and display the form
            $this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
            return $this->renderPage($this->pathViews . DIRECTORY_SEPARATOR . 'forgot_password', $this->data);
        } else {
            $identityColumn = $this->configAtomicAuth->identity;
            $identity = $this->atomicAuth->where($identityColumn, $this->request->getPost('identity'))->users()->row();

            if (empty($identity)) {
                if ($this->configAtomicAuth->identity !== 'email') {
                    $this->atomicAuth->setError('Auth.forgot_password_identity_not_found');
                } else {
                    $this->atomicAuth->setError('Auth.forgot_password_email_not_found');
                }

                $this->session->setFlashdata('message', $this->atomicAuth->errors($this->validationListTemplate));
                return redirect()->to('/auth/forgot_password');
            }

            // run the forgotten password method to email an activation code to the user
            $forgotten = $this->atomicAuth->forgottenPassword($identity->{$this->configAtomicAuth->identity});

            if ($forgotten) {
                // if there were no errors
                $this->session->setFlashdata('message', $this->atomicAuth->messages());
                return redirect()->to('/auth/login'); //we should display a confirmation page here instead of the login page
            } else {
                $this->session->setFlashdata('message', $this->atomicAuth->errors($this->validationListTemplate));
                return redirect()->to('/auth/forgot_password');
            }
        }
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
                $this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

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
                        $this->session->setFlashdata('message', $this->atomicAuth->messages());
                        return redirect()->to('/auth/login');
                    } else {
                        $this->session->setFlashdata('message', $this->atomicAuth->errors($this->validationListTemplate));
                        return redirect()->to('/auth/reset_password/' . $code);
                    }
                }
            }
        } else {
            // if the code is invalid then send them back to the forgot password page
            $this->session->setFlashdata('message', $this->atomicAuth->errors($this->validationListTemplate));
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
        } elseif ($this->atomicAuth->isAdmin()) {
            $activation = $this->atomicAuth->activate($id);
        }

        if ($activation) {
            // redirect them to the auth page
            $this->session->setFlashdata('message', $this->atomicAuth->messages());
            return redirect()->to('/auth');
        } else {
            // redirect them to the forgot password page
            $this->session->setFlashdata('message', $this->atomicAuth->errors($this->validationListTemplate));
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
        if (! $this->atomicAuth->loggedIn() || ! $this->atomicAuth->isAdmin()) {
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
                if ($this->atomicAuth->loggedIn() && $this->atomicAuth->isAdmin()) {
                    $message = $this->atomicAuth->deactivate($id) ? $this->atomicAuth->messages() : $this->atomicAuth->errors($this->validationListTemplate);
                    $this->session->setFlashdata('message', $message);
                }
            }

            // redirect them back to the auth page
            return redirect()->to('/auth');
        }
    }

    /**
     * Create a new user
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function create_user()
    {
        $this->data['title'] = lang('Auth.create_user_heading');

        if (! $this->atomicAuth->loggedIn() || ! $this->atomicAuth->isAdmin()) {
            return redirect()->to('/auth');
        }

        $tables                        = $this->configAtomicAuth->tables;
        $identityColumn                = $this->configAtomicAuth->identity;
        $this->data['identity_column'] = $identityColumn;

        // validate form input
        $this->validation->setRule('first_name', lang('Auth.create_user_validation_fname_label'), 'trim|required');
        $this->validation->setRule('last_name', lang('Auth.create_user_validation_lname_label'), 'trim|required');
        if ($identityColumn !== 'email') {
            $this->validation->setRule('identity', lang('Auth.create_user_validation_identity_label'), 'trim|required|is_unique[' . $tables['users'] . '.' . $identityColumn . ']');
            $this->validation->setRule('email', lang('Auth.create_user_validation_email_label'), 'trim|required|valid_email');
        } else {
            $this->validation->setRule('email', lang('Auth.create_user_validation_email_label'), 'trim|required|valid_email|is_unique[' . $tables['users'] . '.email]');
        }
        $this->validation->setRule('phone', lang('Auth.create_user_validation_phone_label'), 'trim');
        $this->validation->setRule('company', lang('Auth.create_user_validation_company_label'), 'trim');
        $this->validation->setRule('password', lang('Auth.create_user_validation_password_label'), 'required|min_length[' . $this->configAtomicAuth->minPasswordLength . ']|matches[password_confirm]');
        $this->validation->setRule('password_confirm', lang('Auth.create_user_validation_password_confirm_label'), 'required');

        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
            $email    = strtolower($this->request->getPost('email'));
            $identity = ($identityColumn === 'email') ? $email : $this->request->getPost('identity');
            $password = $this->request->getPost('password');

            $additionalData = [
                'first_name' => $this->request->getPost('first_name'),
                'last_name'  => $this->request->getPost('last_name'),
                'company'    => $this->request->getPost('company'),
                'phone'      => $this->request->getPost('phone'),
            ];
        }
        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run() && $this->atomicAuth->register($identity, $password, $email, $additionalData)) {
            // check to see if we are creating the user
            // redirect them back to the admin page
            $this->session->setFlashdata('message', $this->atomicAuth->messages());
            return redirect()->to('/auth');
        } else {
            // display the create user form
            // set the flash data error message if there is one
            $this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : ($this->atomicAuth->errors($this->validationListTemplate) ? $this->atomicAuth->errors($this->validationListTemplate) : $this->session->getFlashdata('message'));

            $this->data['first_name'] = [
                'name'  => 'first_name',
                'id'    => 'first_name',
                'type'  => 'text',
                'value' => set_value('first_name'),
            ];
            $this->data['last_name'] = [
                'name'  => 'last_name',
                'id'    => 'last_name',
                'type'  => 'text',
                'value' => set_value('last_name'),
            ];
            $this->data['identity'] = [
                'name'  => 'identity',
                'id'    => 'identity',
                'type'  => 'text',
                'value' => set_value('identity'),
            ];
            $this->data['email'] = [
                'name'  => 'email',
                'id'    => 'email',
                'type'  => 'email',
                'value' => set_value('email'),
            ];
            $this->data['company'] = [
                'name'  => 'company',
                'id'    => 'company',
                'type'  => 'text',
                'value' => set_value('company'),
            ];
            $this->data['phone'] = [
                'name'  => 'phone',
                'id'    => 'phone',
                'type'  => 'text',
                'value' => set_value('phone'),
            ];
            $this->data['password'] = [
                'name'  => 'password',
                'id'    => 'password',
                'type'  => 'password',
                'value' => set_value('password'),
            ];
            $this->data['password_confirm'] = [
                'name'  => 'password_confirm',
                'id'    => 'password_confirm',
                'type'  => 'password',
                'value' => set_value('password_confirm'),
            ];

            return $this->renderPage($this->pathViews . DIRECTORY_SEPARATOR . 'create_user', $this->data);
        }
    }

    /**
     * Redirect a user checking if is admin
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function redirect()
    {
        if ($this->atomicAuth->isAdmin()) {
            return redirect()->to('/auth');
        }
        return redirect()->to('/');
    }

    /**
     * Edit a user
     *
     * @param integer $id User id
     *
     * @return string string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function edit_user(int $id)
    {
        $this->data['title'] = lang('Auth.edit_user_heading');

        if (! $this->atomicAuth->loggedIn() || (! $this->atomicAuth->isAdmin() && ! ($this->atomicAuth->user()->row()->id == $id))) {
            return redirect()->to('/auth');
        }

        $user          = $this->atomicAuth->user($id)->row();
        $roles        = $this->atomicAuth->roles()->resultArray();
        $currentRoles = $this->atomicAuth->getUserRoles($id)->getResult();

        if (! empty($_POST)) {
            // validate form input
            $this->validation->setRule('first_name', lang('Auth.edit_user_validation_fname_label'), 'trim|required');
            $this->validation->setRule('last_name', lang('Auth.edit_user_validation_lname_label'), 'trim|required');
            $this->validation->setRule('phone', lang('Auth.edit_user_validation_phone_label'), 'trim|required');
            $this->validation->setRule('company', lang('Auth.edit_user_validation_company_label'), 'trim|required');

            // do we have a valid request?
            if ($id !== $this->request->getPost('id', FILTER_VALIDATE_INT)) {
                //show_error(lang('Auth.error_security'));
                throw new \Exception(lang('Auth.error_security'));
            }

            // update the password if it was posted
            if ($this->request->getPost('password')) {
                $this->validation->setRule('password', lang('Auth.edit_user_validation_password_label'), 'required|min_length[' . $this->configAtomicAuth->minPasswordLength . ']|matches[password_confirm]');
                $this->validation->setRule('password_confirm', lang('Auth.edit_user_validation_password_confirm_label'), 'required');
            }

            if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
                $data = [
                    'first_name' => $this->request->getPost('first_name'),
                    'last_name'  => $this->request->getPost('last_name'),
                    'company'    => $this->request->getPost('company'),
                    'phone'      => $this->request->getPost('phone'),
                ];

                // update the password if it was posted
                if ($this->request->getPost('password')) {
                    $data['password'] = $this->request->getPost('password');
                }

                // Only allow updating roles if user is admin
                if ($this->atomicAuth->isAdmin()) {
                    // Update the roles user belongs to
                    $roleData = $this->request->getPost('roles');

                    if (! empty($roleData)) {
                        $this->atomicAuth->removeUserFromRole('', $id);

                        foreach ($roleData as $role) {
                            $this->atomicAuth->addToGroup($role, $id);
                        }
                    }
                }

                // check to see if we are updating the user
                if ($this->atomicAuth->update($user->id, $data)) {
                    $this->session->setFlashdata('message', $this->atomicAuth->messages());
                } else {
                    $this->session->setFlashdata('message', $this->atomicAuth->errors($this->validationListTemplate));
                }
                // redirect them back to the admin page if admin, or to the base url if non admin
                return $this->redirect();
            }
        }

        // display the edit user form

        // set the flash data error message if there is one
        $this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : ($this->atomicAuth->errors($this->validationListTemplate) ? $this->atomicAuth->errors($this->validationListTemplate) : $this->session->getFlashdata('message'));

        // pass the user to the view
        $this->data['user']          = $user;
        $this->data['roles']        = $roles;
        $this->data['currentRoles'] = $currentRoles;

        $this->data['first_name'] = [
            'name'  => 'first_name',
            'id'    => 'first_name',
            'type'  => 'text',
            'value' => set_value('first_name', $user->first_name ?: ''),
        ];
        $this->data['last_name'] = [
            'name'  => 'last_name',
            'id'    => 'last_name',
            'type'  => 'text',
            'value' => set_value('last_name', $user->last_name ?: ''),
        ];
        $this->data['company'] = [
            'name'  => 'company',
            'id'    => 'company',
            'type'  => 'text',
            'value' => set_value('company', empty($user->company) ? '' : $user->company),
        ];
        $this->data['phone'] = [
            'name'  => 'phone',
            'id'    => 'phone',
            'type'  => 'text',
            'value' => set_value('phone', empty($user->phone) ? '' : $user->phone),
        ];
        $this->data['password'] = [
            'name' => 'password',
            'id'   => 'password',
            'type' => 'password',
        ];
        $this->data['password_confirm'] = [
            'name' => 'password_confirm',
            'id'   => 'password_confirm',
            'type' => 'password',
        ];
        $this->data['atomicAuth'] = $this->atomicAuth;

        return $this->renderPage($this->pathViews . DIRECTORY_SEPARATOR . 'edit_user', $this->data);
    }

    /**
     * Create a new role
     *
     * @return string string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function create_role()
    {
        $this->data['title'] = lang('Auth.create_role_title');

        if (! $this->atomicAuth->loggedIn() || ! $this->atomicAuth->isAdmin()) {
            return redirect()->to('/auth');
        }

        // validate form input
        $this->validation->setRule('role_name', lang('Auth.create_role_validation_name_label'), 'trim|required|alpha_dash');

        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
            $newGroupId = $this->atomicAuth->createGroup($this->request->getPost('role_name'), $this->request->getPost('description'));
            if ($newGroupId) {
                // check to see if we are creating the role
                // redirect them back to the admin page
                $this->session->setFlashdata('message', $this->atomicAuth->messages());
                return redirect()->to('/auth');
            }
        } else {
            // display the create role form
            // set the flash data error message if there is one
            $this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : ($this->atomicAuth->errors($this->validationListTemplate) ? $this->atomicAuth->errors($this->validationListTemplate) : $this->session->getFlashdata('message'));

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

        if (! $this->atomicAuth->loggedIn() || ! $this->atomicAuth->isAdmin()) {
            return redirect()->to('/auth');
        }

        $role = $this->atomicAuth->role($id)->row();

        // validate form input
        $this->validation->setRule('role_name', lang('Auth.edit_role_validation_name_label'), 'required|alpha_dash');

        if ($this->request->getPost()) {
            if ($this->validation->withRequest($this->request)->run()) {
                $roleUpdate = $this->atomicAuth->updateGroup($id, $this->request->getPost('role_name'), ['description' => $this->request->getPost('role_description')]);

                if ($roleUpdate) {
                    $this->session->setFlashdata('message', lang('Auth.edit_role_saved'));
                } else {
                    $this->session->setFlashdata('message', $this->atomicAuth->errors($this->validationListTemplate));
                }
                return redirect()->to('/auth');
            }
        }

        // set the flash data error message if there is one
        $this->data['message'] = $this->validation->listErrors($this->validationListTemplate) ?: ($this->atomicAuth->errors($this->validationListTemplate) ?: $this->session->getFlashdata('message'));

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
