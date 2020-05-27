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
class User extends \CodeIgniter\Controller
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
		helper(['form', 'url','AtomicAuth\auth']);
		$this->configAtomicAuth = config('AtomicAuth');
		$this->session       = \Config\Services::session();

		if (! empty($this->configAtomicAuth->templates['errors']['list']))
		{
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
		if (! $this->atomicAuth->loggedIn())
		{
			// redirect them to the login page
			return redirect()->to('/auth/login');
		}
		else if (! $this->atomicAuth->isAdmin()) // remove this elseif if you want to enable this for non-admins
		{
			// redirect them to the home page because they must be an administrator to view this
			//show_error('You must be an administrator to view this page.');
			throw new \Exception('You must be an administrator to view this page.');
		}
		else
		{
			$this->data['title'] = lang('Auth.index_heading');

			// set the flash data error message if there is one
			$this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
			//list the users
			$this->data['users'] = $this->atomicAuth->users()->result();
			foreach ($this->data['users'] as $k => $user)
			{
				$this->data['users'][$k]->roles = $this->atomicAuth->getUserRoles($user->id)->getResult();
			}
			return $this->renderPage($this->pathViews . DIRECTORY_SEPARATOR . 'index', $this->data);
		}
	}

	public function list( string $filterUserStatus = null )
	{
		if (! $this->atomicAuth->loggedIn())
		{
			// redirect to the login page
			return redirect()->to('/auth/login');
		}
		else if ( ! $this->atomicAuth->userCan('list_user')) {
			// redirect unauthorized user to the profile page
			return redirect()->to('/auth/user');
		}

		$this->data['title'] = lang('Auth.list_users_heading');
		$this->data['atomicAuth'] = $this->atomicAuth;
		$this->data['message'] = $this->session->getFlashdata('message');
		$filterUserStatus || $filterUserStatus = 'active';

		if( $filterUserStatus == 'all' )
		{
			// pass the user to the view
			$this->data['users']          = $this->atomicAuth->userModel()->findAll();
		}
		else
		{
			// pass the user to the view
			$this->data['users']          = $this->atomicAuth->userModel()->where('status', $filterUserStatus )->findAll();
		}

		return view('AtomicAuth\Views\Auth\user_list', $this->data);
	}

	public function profile()
	{
		if (! $this->atomicAuth->loggedIn())
		{
			// redirect them to the login page
			return redirect()->to('/auth/login');
		}

		$user          = $this->atomicAuth->getUserProfile();

		if(is_null($user))
		{
			// TODO better handling if user doesn't exist
			return redirect()->to('/auth/login');
		}

		$this->data['title'] = lang('Auth.edit_user_heading');
		$this->data['atomicAuth'] = $this->atomicAuth;
		$this->data['message'] = $this->session->getFlashdata('message');

		// pass the user to the view
		$this->data['user']          = $user;

		return view('AtomicAuth\Views\Auth\user_profile', $this->data);
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

		if (! $this->atomicAuth->loggedIn())
		{
			return redirect()->to('/auth/login');
		}

		$user = $this->atomicAuth->user()->row();

		if ($this->validation->run() === false)
		{
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
		}
		else
		{
			$identity = $this->session->get('identity');

			$change = $this->atomicAuth->changePassword($identity, $this->request->getPost('old'), $this->request->getPost('new'));

			if ($change)
			{
				//if the password was successfully changed
				$this->session->setFlashdata('message', $this->atomicAuth->messages());
				$this->logout();
			}
			else
			{
				$this->session->setFlashdata('message', $this->atomicAuth->errors($this->validationListTemplate));
				return redirect()->to('/auth/change_password');
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
		if (! $code)
		{
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$this->data['title'] = lang('Auth.reset_password_heading');

		$user = $this->atomicAuth->forgottenPasswordCheck($code);

		if ($user)
		{
			// if the code is valid then display the password reset form

			$this->validation->setRule('new', lang('Auth.reset_password_validation_new_password_label'), 'required|min_length[' . $this->configAtomicAuth->minPasswordLength . ']|matches[new_confirm]');
			$this->validation->setRule('new_confirm', lang('Auth.reset_password_validation_new_password_confirm_label'), 'required');

			if (! $this->request->getPost() || $this->validation->withRequest($this->request)->run() === false)
			{
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
			}
			else
			{
				$identity = $user->{$this->configAtomicAuth->identity};

				// do we have a valid request?
				if ($user->id != $this->request->getPost('user_id'))
				{
					// something fishy might be up
					$this->atomicAuth->clearForgottenPasswordCode($identity);

					throw new \Exception(lang('Auth.error_security'));
				}
				else
				{
					// finally change the password
					$change = $this->atomicAuth->resetPassword($identity, $this->request->getPost('new'));

					if ($change)
					{
						// if the password was successfully changed
						$this->session->setFlashdata('message', $this->atomicAuth->messages());
						return redirect()->to('/auth/user');
					}
					else
					{
						$this->session->setFlashdata('message', $this->atomicAuth->errors($this->validationListTemplate));
						return redirect()->to('/auth/reset_password/' . $code);
					}
				}
			}
		}
		else
		{
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

		if ($code)
		{
			$activation = $this->atomicAuth->activate($id, $code);
		}
		else if ($this->atomicAuth->isAdmin())
		{
			$activation = $this->atomicAuth->activate($id);
		}

		if ($activation)
		{
			// redirect them to the auth page
			$this->session->setFlashdata('message', $this->atomicAuth->messages());
			return redirect()->to('/auth');
		}
		else
		{
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
		if (! $this->atomicAuth->loggedIn() || ! $this->atomicAuth->isAdmin())
		{
			// redirect them to the home page because they must be an administrator to view this
			throw new \Exception('You must be an administrator to view this page.');
			// TODO : I think it could be nice to have a dedicated exception like '\AtomicAuth\Exception\NotAllowed
		}

		$this->validation->setRule('confirm', lang('Auth.deactivate_validation_confirm_label'), 'required');
		$this->validation->setRule('id', lang('Auth.deactivate_validation_user_id_label'), 'required|integer');

		if (! $this->validation->withRequest($this->request)->run())
		{
			$this->data['user'] = $this->atomicAuth->user($id)->row();
			return $this->renderPage($this->pathViews . DIRECTORY_SEPARATOR . 'deactivate_user', $this->data);
		}
		else
		{
			// do we really want to deactivate?
			if ($this->request->getPost('confirm') === 'yes')
			{
				// do we have a valid request?
				if ($id !== $this->request->getPost('id', FILTER_VALIDATE_INT))
				{
					throw new \Exception(lang('Auth.error_security'));
				}

				// do we have the right userlevel?
				if ($this->atomicAuth->loggedIn() && $this->atomicAuth->isAdmin())
				{
					$message = $this->atomicAuth->deactivate($id) ?
						$this->atomicAuth->messages() :
						$this->atomicAuth->errors($this->validationListTemplate);

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
	public function create()
	{
		$this->data['title'] = lang('Auth.create_user_heading');

		// TODO lock down for unauthorized request vs admin create
		if ($this->configAtomicAuth->forceAuthorizedUserCreate &&
			// TODO should we limit only to admin?
			(! $this->atomicAuth->loggedIn() /*|| ! $this->atomicAuth->isAdmin() */ ))
		{
			return redirect()->to('/auth');
		}

		$this->data['identity_column'] = $this->configAtomicAuth->identity;
		$this->data['message'] = $this->session->getFlashdata('message');

		// set validation rules
		if ($this->configAtomicAuth->identity !== 'email')
		{
			$this->validation->setRule('identity', lang('Auth.create_user_validation_identity_label'), 'trim|required|is_unique[' . $this->configAtomicAuth->tables['users'] . '.' . $this->configAtomicAuth->identity . ']');
			$this->validation->setRule('email', lang('Auth.create_user_validation_email_label'), 'trim|required|valid_email');
		}
		else
		{
			$this->validation->setRule('email', lang('Auth.create_user_validation_email_label'), 'trim|required|valid_email|is_unique[' . $this->configAtomicAuth->tables['users'] . '.email]');
		}
		$this->validation->setRule('password', lang('Auth.create_user_validation_password_label'), 'required|min_length[' . $this->configAtomicAuth->minPasswordLength . ']|matches[password_confirm]');
		$this->validation->setRule('password_confirm', lang('Auth.create_user_validation_password_confirm_label'), 'required');


		// parse submitted request
		if ( $this->request->getPost() ){

				// run validation
				if($this->validation->withRequest($this->request)->run())
				{
					$email    = strtolower($this->request->getPost('email'));
					$identity = strtolower($this->request->getPost($this->configAtomicAuth->identity));
					$password = $this->request->getPost('password');
					$userMeta = []; // TODO flesh out user meta data
					$userRoles = []; // TODO flesh out user role associations

					// user entity register the user
					if( $this->atomicAuth->register($identity, $password, $email, $userMeta, $userRoles) )
					{
						// check to see if we are creating the user
						// redirect them back to the admin page
						$this->session->setFlashdata('message', $this->atomicAuth->messages());

						// redirect vs render response
						if( $this->configAtomicAuth->redirectOnSuccess ) {
							return redirect()->to('/auth');
						} else {
							$this->data['message'] = $this->session->getFlashdata('message');
						}

					}
					else
					{
						$this->data['message'] = $this->atomicAuth->errors($this->validationListTemplate) ?
							$this->atomicAuth->errors($this->validationListTemplate) :
							$this->data['message'];
					}
				}
				else
				{
					// display the create user form
					// set the flash data error message if there is one
					$this->data['message'] = $this->validation->getErrors() ?
						$this->validation->listErrors($this->validationListTemplate) :
						$this->data['message'];

				}

						}

				$this->data['email'] = [
					'name'  => 'email',
					'id'    => 'email',
					'type'  => 'email',
					'value' => set_value('email'),
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

				// render response vs redirect
				return view('AtomicAuth\Views\Auth\user_create', $this->data);

	}

	/**
	 * Redirect a user checking if is admin
	 *
	 * @return \CodeIgniter\HTTP\RedirectResponse
	 */
	public function redirect()
	{
		if ($this->atomicAuth->isAdmin())
		{
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
	public function edit(string $guid = null)
	{
		$this->data['title'] = lang('Auth.edit_user_heading');

		// TODO secure this page
		if (
			! $this->atomicAuth->loggedIn()
			// || ! $this->atomicAuth->userCan('edit_user')
			// || ($this->atomicAuth->userCan('edit_self') && !is_null($guid))
			)
		{
			return redirect()->to('/auth');
		}

		$refreshSession = false;

		if( $this->request->getPost('attomic_auth_user_id') )
		{
			$user_id = $this->request->getPost('attomic_auth_user_id');
		}
		else if( is_null( $guid ) )
		{
			$user_id = $this->atomicAuth->getSessionProperty('id');
		}
		else
		{
			$lookupUserId = $this->atomicAuth->userModel()->getUserByGuid($guid);
			$user_id = $lookupUserId->id;
		}
		// TODO better handling if user doesn't exist
		if(is_null($user_id))
		{
			return redirect()->to('/auth/create');
		}


		$this->data['atomicAuth'] = $this->atomicAuth;
		$this->data['message'] = $this->session->getFlashdata('message');

				// parse submitted request
				if ( $this->request->getPost() ){


					// dd($this->request->getPost());

					/* TODO is this needed?
					// do we have a valid request?
					if ($guid !== $this->request->getPost('guid', FILTER_VALIDATE_INT))
					{
						//show_error(lang('Auth.error_security'));
						throw new \Exception(lang('Auth.error_security'));
					}
					*/

					// $email    = strtolower($this->request->getPost('email'));
					// $identity = strtolower($this->request->getPost($this->configAtomicAuth->identity));

					$userMeta = []; // TODO flesh out user meta data
					$userData = (object)[];
					$userData->roleIds = $this->request->getPost('roles');
					$userData->status = $this->request->getPost('status');

					// check to see if we are updating the user
					if ($this->atomicAuth->update($user_id, $userData))
					{
						$refreshSession = true;
						$this->session->setFlashdata('message', $this->atomicAuth->messages());
					}
					else
					{
						$this->session->setFlashdata('message', $this->atomicAuth->errors($this->validationListTemplate));
					}



						// run validation
						if($this->validation->withRequest($this->request)->run())
						{

												$password = $this->request->getPost('password');
												// update the password if it was posted
												if ($password)
												{
													$this->validation->setRule('password', lang('Auth.edit_user_validation_password_label'), 'required|min_length[' . $this->configAtomicAuth->minPasswordLength . ']|matches[password_confirm]');
													$this->validation->setRule('password_confirm', lang('Auth.edit_user_validation_password_confirm_label'), 'required');
												}

							// user entity register the user
							// if( $this->atomicAuth->register($identity, $password, $email, $userMeta, $userRoles) )
							// {
							// 	// check to see if we are creating the user
							// 	// redirect them back to the admin page
							// 	$this->session->setFlashdata('message', $this->atomicAuth->messages());
							//
							// 	// redirect vs render response
							// 	if( $this->configAtomicAuth->redirectOnSuccess ) {
							// 		return redirect()->to('/auth');
							// 	} else {
							// 		$this->data['message'] = $this->session->getFlashdata('message');
							// 	}
							//
							// }
							// else
							// {
							// 	$this->data['message'] = $this->atomicAuth->errors($this->validationListTemplate) ?
							// 		$this->atomicAuth->errors($this->validationListTemplate) :
							// 		$this->data['message'];
							// }
						}
						else
						{
							// display the create user form
							// set the flash data error message if there is one
							$this->data['message'] = $this->validation->getErrors() ?
								$this->validation->listErrors($this->validationListTemplate) :
								$this->data['message'];

						}






								}
/*
		if (! empty($_POST))
		{





			if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
			{
				$data = [
					'first_name' => $this->request->getPost('first_name'),
					'last_name'  => $this->request->getPost('last_name'),
					'company'    => $this->request->getPost('company'),
					'phone'      => $this->request->getPost('phone'),
				];

				// update the password if it was posted
				if ($this->request->getPost('password'))
				{
					$data['password'] = $this->request->getPost('password');
				}

				// Only allow updating roles if user is admin
				if ($this->atomicAuth->isAdmin())
				{
					// Update the roles user belongs to
					$roleData = $this->request->getPost('roles');

					if (! empty($roleData))
					{
						$this->atomicAuth->removeFromGroup('', $id);

						foreach ($roleData as $role)
						{
							$this->atomicAuth->addToGroup($role, $id);
						}
					}
				}

				// check to see if we are updating the user
				if ($this->atomicAuth->update($user->id, $data))
				{
					$this->session->setFlashdata('message', $this->atomicAuth->messages());
				}
				else
				{
					$this->session->setFlashdata('message', $this->atomicAuth->errors($this->validationListTemplate));
				}
				// redirect them back to the admin page if admin, or to the base url if non admin
				return $this->redirect();
			}
		}
*/
		// display the edit user form

		// set the flash data error message if there is one
		// $this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : ($this->atomicAuth->errors($this->validationListTemplate) ? $this->atomicAuth->errors($this->validationListTemplate) : $this->session->getFlashdata('message'));

		$user = $this->atomicAuth->getUserProfile( $user_id, 'id' );

		if( $refreshSession && $user_id == $this->atomicAuth->getSessionProperty('id') )
		{

			$this->atomicAuth->setSession( $user );
		}

		// pass the user to the view
		$this->data['user']          = $user;

		// TODO figure out role status to be 'active' => 1 in a cleaner way
		$roleEntity = new \AtomicAuth\Entities\Role();
		$this->data['roles']        = $this->atomicAuth->roleModel()->where('status', $roleEntity->statusValueMap['active'])->findAll();
		$this->data['userInRoles']  = array_column($user->roles, 'id');

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


		// render response vs redirect
		return view('AtomicAuth\Views\Auth\user_edit', $this->data);
	}
}
