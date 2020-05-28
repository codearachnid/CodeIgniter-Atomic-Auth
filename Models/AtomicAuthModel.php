<?php namespace AtomicAuth\Models;

/**
 * Name:    Atomic Auth Model
 *
 * Created:  10.01.2009
 *
 * Description:  Modified auth system based on redux_auth with extensive customization.
 *               This is basically what Redux Auth 2 should be.
 * Original Author name has been kept but that does not mean that the method has not been modified.
 *
 * Requirements: PHP 7.2 or above
 *
 * @package    CodeIgniter-Atomic-Auth
 * @Author		 Timothy Wood <codearachnid@gmail.com>
 * @author     Ben Edmunds <ben.edmunds@gmail.com>
 * @author     Phil Sturgeon
 * @author     Benoit VRIGNAUD <benoit.vrignaud@tangue.fr>
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       http://github.com/benedmunds/CodeIgniter-Ion-Auth
 * @filesource
 */

use \CodeIgniter\Database\ConnectionInterface;

/**
 * Class AtomicAuthModel
 *
 * @property Atomic_auth $atomic_auth The Atomic_auth library
 */
class AtomicAuthModel
{
	/**
	 * Max cookie lifetime constant
	 */
	const MAX_COOKIE_LIFETIME = 63072000; // 2 years = 60*60*24*365*2 = 63072000 seconds;

	/**
	 * Max password size constant
	 */
	const MAX_PASSWORD_SIZE_BYTES = 4096;

	/**
	 * AtomicAuth config
	 *
	 * @var Config\AtomicAuth
	 */
	protected $config;

	/**
	 * CodeIgniter session
	 *
	 * @var \CodeIgniter\Session\Session
	 */
	protected $session;

	/**
	 * AtomicAuth model
	 *
	 * @var \AtomicAuth\Models\*
	 */
	protected $userModel;
	protected $roleModel;
	protected $loginModel;
	protected $capabilityModel;

	/**
	 * Activation code
	 *
	 * Set by deactivate() function
	 * Also set on register() function, if email_activation
	 * option is activated
	 *
	 * This is the value devs should give to the user
	 * (in an email, usually)
	 *
	 * It contains the *user* version of the activation code
	 * It's a value of the form "selector.validator"
	 *
	 * This is not the same activationCode as the one in DB.
	 * The DB contains a *hashed* version of the validator
	 * and a selector in another column.
	 *
	 * THe selector is not private, and only used to lookup
	 * the validator.
	 *
	 * The validator is private, and to be only known by the user
	 * So in case of DB leak, nothing could be actually used.
	 *
	 * @var string
	 */
	public $activationCode;


	/**
	 * Where
	 *
	 * @var array
	 */
	protected $atomicWhere = [];

	/**
	 * Select
	 *
	 * @var array
	 */
	protected $atomicSelect = [];

	/**
	 * Like
	 *
	 * @var array
	 */
	protected $atomicLike = [];

	/**
	 * Limit
	 *
	 * @var string
	 */
	protected $atomicLimit = null;

	/**
	 * Offset
	 *
	 * @var string
	 */
	protected $atomicOffset = null;

	/**
	 * Order By
	 *
	 * @var string
	 */
	protected $atomicOrderBy = null;

	/**
	 * Order
	 *
	 * @var string
	 */
	protected $atomicOrder = null;

	/**
	 * Hooks
	 *
	 * @var object
	 */
	protected $atomicHooks;

	/**
	 * Response
	 *
	 * @var \CodeIgniter\Database\ResultInterface
	 */
	protected $response = null;

	/**
	 * Message (uses lang file)
	 *
	 * @var string
	 */
	protected $messages = [];

	/**
	 * Error message (uses lang file)
	 *
	 * @var string
	 */
	protected $errors = [];

	/**
	 * Message templates (single, list).
	 *
	 * @var array
	 */
	protected $messageTemplates = [];

	/**
	 * Caching of users and their roles
	 *
	 * @var array
	 */
	protected $cacheUserInGroup = [];

	/**
	 * Caching of roles
	 *
	 * @var array
	 */
	protected $cacheRoles = [];

	/**
	 * Database object
	 *
	 * @var \CodeIgniter\Database\BaseConnection
	 */
	protected $db;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->config = config('AtomicAuth');
		helper(['cookie', 'date']);
		$this->session = session();

		// initialize the database
		if (empty($this->config->databaseGroupName))
		{
			// By default, use CI's db that should be already loaded
			$this->db = \Config\Database::connect();
		}
		else
		{
			// For specific role name, open a new specific connection
			$this->db = \Config\Database::connect($this->config->databaseGroupName);
		}

		// initialize our hooks object
		$this->authHooks = new \stdClass();

		$this->userModel = model('AtomicAuth\Models\UserModel');
		$this->roleModel = model('AtomicAuth\Models\RoleModel');
		$this->loginModel = model('AtomicAuth\Models\LoginModel');
		$this->capabilityModel = model('AtomicAuth\Models\CapabilityModel');

		$this->triggerEvents('model_constructor');
	}

	/**
	 * Getter to the DB connection used by Atomic Auth
	 * May prove useful for debugging
	 *
	 * @return object
	 */
	public function db()
	{
		return $this->db;
	}

	/**
	 * Hashes the password to be stored in the database.
	 *
	 * @param string $password Password
	 * @param string $identity Identity
	 *
	 * @return false|string
	 * @author Mathew
	 */
	public function hashPassword(string $password, string $identity='')
	{
		// Check for empty password, or password containing null char, or password above limit
		// Null char may pose issue: http://php.net/manual/en/function.password-hash.php#118603
		// Long password may pose DOS issue (note: strlen gives size in bytes and not in multibyte symbol)
		if (empty($password) || strpos($password, "\0") !== false ||
			strlen($password) > self::MAX_PASSWORD_SIZE_BYTES)
		{
			return false;
		}

		$algo   = $this->getHashAlgo();
		$params = $this->getHashParameters($identity);

		if ($algo !== false && $params !== false)
		{
			return password_hash($password, $algo, $params);
		}

		return false;
	}

	/**
	 * This function takes a password and validates it
	 * against an entry in the users table.
	 *
	 * @param string $password       Password
	 * @param string $hashPasswordDb
	 * @param string $identity		 Optional @deprecated only for BC SHA1
	 *
	 * @return boolean
	 * @author Mathew
	 */
	public function verifyPassword(string $password, string $hashPasswordDb, string $identity=''): bool
	{
		// Check for empty id or password, or password containing null char, or password above limit
		// Null char may pose issue: http://php.net/manual/en/function.password-hash.php#118603
		// Long password may pose DOS issue (note: strlen gives size in bytes and not in multibyte symbol)
		if (empty($password) || empty($hashPasswordDb) || strpos($password, "\0") !== false
			|| strlen($password) > self::MAX_PASSWORD_SIZE_BYTES)
		{
			return false;
		}
		return password_verify($password, $hashPasswordDb);
	}

	/**
	 * Check if password needs to be rehashed
	 * If true, then rehash and update it in DB
	 *
	 * @param string $hash     Hash
	 * @param string $identity Identity
	 * @param string $password Password
	 *
	 * @return void
	 */
	public function rehashPasswordIfNeeded(string $hash, string $identity, string $password): void
	{
		$algo   = $this->getHashAlgo();
		$params = $this->getHashParameters($identity);

		if ($algo !== false && $params !== false)
		{
			if (password_needs_rehash($hash, $algo, $params))
			{
				if ($this->setPasswordDb($identity, $password))
				{
					$this->triggerEvents(['rehash_password', 'rehash_password_successful']);
				}
				else
				{
					$this->triggerEvents(['rehash_password', 'rehash_password_unsuccessful']);
				}
			}
		}
	}

	/**
	 * Get a user by its activation code
	 *
	 * @param string $userCode The activation code
	 *                         It's the *user* one, containing "selector.validator"
	 *                         the one you got in activation_code member
	 *
	 * @return boolean|object
	 * @author Indigo
	 */
	public function getUserByActivationCode(string $userCode)
	{
		// Retrieve the token object from the code
		$token = $this->retrieveSelectorValidatorCouple($userCode);

		// Retrieve the user according to this selector
		$user = $this->where('activation_selector', $token->selector)->users()->row();

		if ($user)
		{
			// Check the hash against the validator
			if ($this->verifyPassword($token->validator, $user->activation_code))
			{
				return $user;
			}
		}

		return false;
	}

	/**
	 * Validates and removes activation code.
	 *
	 * @param integer|string $id   The user identifier
	 * @param string         $code The *user* activation code
	 *                             if omitted, simply activate the user without check
	 *
	 * @return boolean
	 * @author Mathew
	 */
	public function activate($id, string $code=''): bool
	{
		$this->triggerEvents('pre_activate');

		if ($code)
		{
			$user = $this->getUserByActivationCode($code);
		}
		// Activate if no code is given
		// Or if a user was found with this code, and that it matches the id
		if (!$code || ($user && $user->id == $id))
		{
			$data = [
				'activation_selector' => null,
				'activation_code'     => null,
				'active'              => 1,
			];

			$this->triggerEvents('extra_where');
			$this->db->table($this->config->tables['users'])->update($data, ['id' => $id]);

			if ($this->db->affectedRows() === 1)
			{
				$this->triggerEvents(['post_activate', 'post_activate_successful']);
				$this->setMessage('AtomicAuth.activate_successful');
				return true;
			}
		}

		$this->triggerEvents(['post_activate', 'post_activate_unsuccessful']);
		$this->setError('AtomicAuth.activate_unsuccessful');
		return false;
	}

	/**
	 * Updates a users row with an activation code.
	 *
	 * @param integer $id User id
	 *
	 * @return boolean
	 * @author Mathew
	 */
	public function deactivate(int $id=0): bool
	{
		$this->triggerEvents('deactivate');

		if (! $id)
		{
			$this->setError('AtomicAuth.deactivate_unsuccessful');
			return false;
		}
		else if ((new \AtomicAuth\Libraries\AtomicAuth())->loggedIn() && $this->user()->row()->id == $id)
		{
			$this->setError('AtomicAuth.deactivate_current_user_unsuccessful');
			return false;
		}

		$token                = $this->generateSelectorValidatorCouple(20, 40);
		$this->activationCode = $token->userCode;

		$data = [
			'activation_selector' => $token->selector,
			'activation_code'     => $token->validatorHashed,
			'active'              => 0,
		];

		$this->triggerEvents('extra_where');
		$this->db->table($this->config->tables['users'])->update($data, ['id' => $id]);

		$return = $this->db->affectedRows() === 1;
		if ($return)
		{
			$this->setMessage('AtomicAuth.deactivate_successful');
		}
		else
		{
			$this->setError('AtomicAuth.deactivate_unsuccessful');
		}

		return $return;
	}

	/**
	 * Clear the forgotten password code for a user
	 *
	 * @param string $identity Identity
	 *
	 * @return boolean Success
	 */
	public function clearForgottenPasswordCode(string $identity): bool
	{
		if (empty($identity))
		{
			return false;
		}

		$data = [
			'forgotten_password_selector' => null,
			'forgotten_password_code'     => null,
			'forgotten_password_time'     => null,
		];

		return $this->db->table($this->config->tables['users'])->update($data, [$this->config->identity => $identity]);
	}

	/**
	 * Clear the remember code for a user
	 *
	 * @param string $identity Identity
	 *
	 * @return boolean Success
	 */
	public function clearRememberCode(string $identity): bool
	{
		if (empty($identity))
		{
			return false;
		}

		$data = [
			'remember_selector' => null,
			'remember_code'     => null,
		];

		return $this->db->table($this->config->tables['users'])->update($data, [$this->config->identity => $identity]);
	}

	/**
	 * Reset password
	 *
	 * @param string $identity Identity
	 * @param string $new      New password
	 *
	 * @return boolean
	 * @author Mathew
	 */
	public function resetPassword(string $identity, string $new)
	{
		$this->triggerEvents('pre_change_password');

		if (! $this->identityCheck($identity))
		{
			$this->triggerEvents(['post_change_password', 'post_change_password_unsuccessful']);
			return false;
		}

		$return = $this->setPasswordDb($identity, $new);

		if ($return)
		{
			$this->triggerEvents(['post_change_password', 'post_change_password_successful']);
			$this->setMessage('AtomicAuth.password_change_successful');
		}
		else
		{
			$this->triggerEvents(['post_change_password', 'post_change_password_unsuccessful']);
			$this->setError('AtomicAuth.password_change_unsuccessful');
		}

		return $return;
	}

	/**
	 * Change password
	 *
	 * @param string $identity Identity
	 * @param string $old      Old password
	 * @param string $new      New password
	 *
	 * @return boolean
	 * @author Mathew
	 */
	public function changePassword(string $identity, string $old, string $new): bool
	{
		$this->triggerEvents('pre_change_password');

		$this->triggerEvents('extra_where');

		$builder = $this->db->table($this->config->tables['users']);
		$query   = $builder
					   ->select('id, password')
					   ->where($this->config->identity, $identity)
					   ->limit(1)
					   ->get()->getResult();

		if (empty($query))
		{
			$this->triggerEvents(['post_change_password', 'post_change_password_unsuccessful']);
			$this->setError('AtomicAuth.password_change_unsuccessful');
			return false;
		}

		$user = $query[0];

		if ($this->verifyPassword($old, $user->password, $identity))
		{
			$result = $this->setPasswordDb($identity, $new);

			if ($result)
			{
				$this->triggerEvents(['post_change_password', 'post_change_password_successful']);
				$this->setMessage('AtomicAuth.password_change_successful');
			}
			else
			{
				$this->triggerEvents(['post_change_password', 'post_change_password_unsuccessful']);
				$this->setError('AtomicAuth.password_change_unsuccessful');
			}

			return $result;
		}

		$this->setError('AtomicAuth.password_change_unsuccessful');
		return false;
	}

	/**
	 * Checks username
	 *
	 * @param string $username User name
	 *
	 * @return boolean
	 * @author Mathew
	 */
	public function usernameCheck(string $username): bool
	{
		$this->triggerEvents('username_check');

		if (empty($username))
		{
			return false;
		}

		$this->triggerEvents('extra_where');

		return $this->db->where('username', $username)
						->limit(1)
						->count_all_results($this->config->tables['users']) > 0;
	}

	/**
	 * Checks email to see if the email is already registered.
	 *
	 * @param string $email Email to check
	 *
	 * @return boolean true if the user is registered false if the user is not registered.
	 * @author Mathew
	 */
	public function emailCheck(string $email=''): bool
	{
		$this->triggerEvents('emailCheck');

		if (empty($email))
		{
			return false;
		}

		$this->triggerEvents('extra_where');

		return $this->db->table($this->config->tables['users'])
						->where('email', $email)
						->limit(1)
						->countAllResults() > 0;
	}

	/**
	 * Identity check : Check to see if the identity is already registered
	 *
	 * @param string $identity Identity
	 *
	 * @return boolean
	 * @author Mathew
	 */
	public function identityCheck(string $identity=''): bool
	{
		$this->triggerEvents('identity_check');

		if (empty($identity))
		{
			return false;
		}

		$builder = $this->db->table($this->config->tables['users']);
		return $builder->where($this->config->identity, $identity)
					   ->limit(1)
					   ->countAllResults() > 0;
	}

	/**
	 * Get user ID from identity
	 *
	 * @param string $identity Identity
	 *
	 * @return boolean|integer
	 */
	public function getUserIdFromIdentity(string $identity='')
	{
		if (empty($identity))
		{
			return false;
		}

		$builder = $this->db->table($this->config->tables['users']);
		$query = $builder->select('id')
						 ->where($this->config->identity, $identity)
						 ->limit(1)
						 ->get();

		$user = $query->getRow();

		if ($user)
		{
			return $user->id;
		}

		return false;
	}

	/**
	 * Insert a forgotten password key.
	 *
	 * @param string $identity As defined in Config/AtomicAuth
	 *
	 * @return boolean|string
	 *
	 * @author Mathew
	 * @author Ryan
	 */
	public function forgottenPassword(string $identity)
	{
		if (empty($identity))
		{
			$this->triggerEvents(['post_forgotten_password', 'post_forgotten_password_unsuccessful']);
			return false;
		}

		// Generate random token: smaller size because it will be in the URL
		$token = $this->generateSelectorValidatorCouple(20, 80);

		$update = [
			'forgotten_password_selector' => $token->selector,
			'forgotten_password_code'     => $token->validatorHashed,
			'forgotten_password_time'     => time(),
		];

		$this->triggerEvents('extra_where');
		$this->db->table($this->config->tables['users'])->update($update, [$this->config->identity => $identity]);

		if ($this->db->affectedRows() === 1)
		{
			$this->triggerEvents(['post_forgotten_password', 'post_forgotten_password_successful']);
			return $token->userCode;
		}
		else
		{
			$this->triggerEvents(['post_forgotten_password', 'post_forgotten_password_unsuccessful']);
			return false;
		}
	}

	/**
	 * Get a user from a forgotten password key.
	 *
	 * @param string $userCode Forgotten password key
	 *
	 * @return  boolean|object
	 * @author  Mathew
	 * @updated Ryan
	 */
	public function getUserByForgottenPasswordCode(string $userCode)
	{
		// Retrieve the token object from the code
		$token = $this->retrieveSelectorValidatorCouple($userCode);

		// Retrieve the user according to this selector
		$user = $this->where('forgotten_password_selector', $token->selector)->users()->row();

		if ($user)
		{
			// Check the hash against the validator
			if ($this->verifyPassword($token->validator, $user->forgotten_password_code))
			{
				return $user;
			}
		}

		return false;
	}

	/**
	 * Logs the user into the system
	 *
	 * @param string  $identity Username, email or any unique value in your users table, depending on your configuration
	 * @param string  $password Password
	 * @param boolean $remember Sets the user to be remembered if enabled in the configuration
	 *
	 * @return boolean
	 * @author Mathew
	 */
	public function login(string $identity, string $password, bool $remember=false): bool
	{
		$this->triggerEvents('pre_login');

		if (empty($identity) || empty($password))
		{
			$this->setError('AtomicAuth.login_unsuccessful');
			$this->setSession(null);
			return false;
		}

		$this->triggerEvents('extra_where');
		$user = $this->db->table($this->config->tables['users'])
						  ->select([
								$this->config->identity,
								'email',
								'guid',
								'id',
								'password_hash',
								'status',
						  ])->where([
								$this->config->identity => $identity,
								'status' => 'active', // TODO should get any user other than active?
								'force_pass_reset' => 0, // TODO should user be able to login without a password reset?
							])->limit(1)
						  ->orderBy('id', 'desc')
							->get()
							->getRow();

		if ($this->isMaxAttemptsExceeded($identity))
		{
			// Hash something anyway, just to take up time
			// $this->hashPassword($password);
			$this->triggerEvents('post_login_unsuccessful');
			$this->setError('AtomicAuth.login_timeout');
			$this->setLoginAttempt($identity, 'max_attempts');
			return false;
		}


		if(empty($user)){
			$this->triggerEvents('post_login_unsuccessful');
			$this->setError('AtomicAuth.login_unsuccessful_not_exists');
			$this->setSession(null);
			$this->setLoginAttempt($identity, 'not_exist');
			return false;
		}

		if ($this->verifyPassword($password, $user->password_hash, $identity))
		{
				// TODO should user know they aren't active?
				// if ($user->status != 1)
				// {
				// 	$this->triggerEvents('post_login_unsuccessful');
				// 	$this->setError('AtomicAuth.login_unsuccessful_not_active');
				//
				// 	return false;
				// }

				$this->setSession($user);
				$this->setLoginAttempt($user->email, 'success', $user->id);

				// $this->clearLoginAttempts($user->email);
				// $this->clearForgottenPasswordCode($user->email);

				// if ($this->config->rememberUsers)
				// {
				// 	if ($remember)
				// 	{
				// 		$this->rememberUser($identity);
				// 	}
				// 	else
				// 	{
				// 		$this->clearRememberCode($identity);
				// 	}
				// }

				// Rehash if needed
				// $this->rehashPasswordIfNeeded($user->password, $identity, $password);

				$this->triggerEvents(['post_login', 'post_login_successful']);
				$this->setMessage('AtomicAuth.login_successful');

				return true;
		}

		// Hash something anyway, just to take up time
		// $this->hashPassword($password);

		// $this->increaseLoginAttempts($identity);

		$this->triggerEvents('post_login_unsuccessful');
		$this->setError('AtomicAuth.login_unsuccessful');
		$this->setLoginAttempt($identity, 'failed_password');
		$this->setSession(null);
		return false;
	}

	/**
	 * Verifies if the session should be rechecked according to the configuration item recheckTimer. If it does, then
	 * it will check if the user is still active
	 *
	 * @return boolean
	 */

	 //TODO rework this!
	public function recheckSession(): bool
	{
		$sessionExpiration = (null !== $this->config->sessionExpiration) ? $this->config->sessionExpiration : 0;
		$activeUser = $this->session->get('activeUser');

		if ($sessionExpiration !== 0)
		{

			dd($userSession);
			$lastLogin = $this->session->get('last_check');
			if ($lastLogin + $recheck < time())
			{
				$query = $this->db->select('id')
								  ->where([
									  $this->config->identity => $this->session->get('identity'),
									  'status'              => '1',
								  ])
								  ->limit(1)
								  ->orderBy('id', 'desc')
								  ->get($this->config->tables['users']);
				if ($query->numRows() === 1)
				{
					$this->session->set('last_check', time());
				}
				else
				{
					$this->triggerEvents('logout');
					$identity = $this->config->identity;
					$this->session->remove([$identity, 'id', 'user_id']);

					return false;
				}
			}
		}

		return (bool) $activeUser;
	}

	/**
	 * Check if max login attempts exceeded
	 * Based on code from Tank Auth, by Ilya Konyukhov (https://github.com/ilkon/Tank-Auth)
	 *
	 * @param string      $identity  User's identity
	 * @return boolean
	 */
	public function isMaxAttemptsExceeded(string $identity): bool
	{
		if ($this->config->trackAttempts && $this->config->maxAttempts > 0)
		{
			return (bool) ($this->getAttemptsNum($identity) >= $this->config->maxAttempts);
		}
		return false;
	}

	/**
	 * Get number of login attempts for the given IP-address or identity
	 * Based on code from Tank Auth, by Ilya Konyukhov (https://github.com/ilkon/Tank-Auth)
	 *
	 * @param string      $identity  User's identity
	 * @return integer
	 */
	public function getAttemptsNum(string $identity): int
	{
		if ($this->config->trackAttempts)
		{
			$this->loginModel->lockoutTime = $this->config->lockoutTime;
			$this->loginModel->limit = $this->config->maxAttempts;
			$this->loginModel->identity = $identity;
			$logins = $this->loginModel->getLoginsByIdentity();
			return count($logins);
		}
		return 0;
	}

	/**
	 * Get the last time a login attempt occurred from given identity
	 *
	 * @param string      $identity  User's identity
	 * @return integer The time of the last login attempt for a given IP-address or identity
	 */
	public function getLastAttemptTime(string $identity): datetime
	{
		if ($this->config->trackAttempts)
		{
			$this->loginModel->identity = $identity;
			$qres = $this->loginModel->getLoginsByIdentity( true );
			if (count($qres) > 0 )
			{
				return $qres[0]->created_at;
			}
		}

		return 0;
	}

	/**
	 * Get the IP address of the last time a login attempt occurred from given identity
	 *
	 * @param string $identity User's identity
	 *
	 * @return string
	 */
	public function getLastAttemptIp(string $identity)
	{
		if ($this->config->trackAttempts)
		{
			$this->loginModel->identity = $identity;
			$qres = $this->loginModel->getLoginsByIdentity();
			if (count($qres) > 0 )
			{
				return $qres[0]->ip_address;
			}
		}

		return '';
	}

	/**
	 * Based on code from Tank Auth, by Ilya Konyukhov (https://github.com/ilkon/Tank-Auth)
	 *
	 * Note: the current IP address will be used if trackLoginIpAddress config value is true
	 *
	 * @param string $identity User's identity
	 *
	 * @return boolean
	 */
	public function increaseLoginAttempts(string $identity): bool
	{
		if ($this->config->trackAttempts)
		{
			$data = ['ip_address' => '', 'login' => $identity, 'time' => time()];
			if ($this->config->trackLoginIpAddress)
			{
				$data['ip_address'] = \Config\Services::request()->getIPAddress();
			}
			$builder = $this->db->table($this->config->tables['login_attempts']);
			$builder->insert($data);
			return true;
		}
		return false;
	}

	/**
	 * Clear login attempts
	 * Based on code from Tank Auth, by Ilya Konyukhov (https://github.com/ilkon/Tank-Auth)
	 *
	 * @param string      $identity                User's identity
	 * @param integer     $oldAttemptsAxpirePeriod In seconds, any attempts older than this value will be removed.
	 *                                                It is used for regularly purging the attempts table.
	 *                                                (for security reason, minimum value is lockoutTime config value)
	 * @return boolean
	 */
	public function clearLoginAttempts(string $identity, int $oldAttemptsAxpirePeriod=86400): bool
	{
		if ($this->config->trackAttempts)
		{
			// Make sure $oldAttemptsAxpirePeriod is at least equals to lockoutTime
			$this->loginModel->lockoutTime = max($oldAttemptsAxpirePeriod, $this->config->lockoutTime);
			$this->loginModel->identity = $identity;
			$logins = $this->loginModel->getLoginsByIdentity();

			$builder = $this->db->table($this->config->tables['login_attempts']);
			$builder->where('login', $identity);
			if ($this->config->trackLoginIpAddress)
			{
				if (! isset($ipAddress))
				{
					$ipAddress = \Config\Services::request()->getIPAddress();
				}
				$builder->where('ip_address', $ipAddress);
			}
			// Purge obsolete login attempts
			$builder->orWhere('time <', time() - $oldAttemptsAxpirePeriod, false);

			return $builder->delete() === false ? false: true;
		}
		return false;
	}

	public function createUser( string $identity, string $password, string $email, array $userMeta = [] )
	{
		// register User Entity to begin insert
		$user = new \AtomicAuth\Entities\User();

		$user->{$this->config->identity} = $identity;
		$user->password_hash = $this->hashPassword($password, $identity);
		$user->status = 'active'; // TODO there needs to be a better way to activate a user
		// TODO I have a dream that this will work one day *** see after insert for bandaid
		// $builder->set('guid', 'UUID_TO_BIN(UUID())', FALSE);
		$user->guid = $this->userModel->generateGuid();
		$user->id = $this->userModel->insert( $user );

		return $user;
	}

	/**
	 * Check to see if a user is in a role(s)
	 *
	 * @param integer|array $checkGroup Group(s) to check
	 * @param integer       $id         User id
	 * @param boolean       $checkAll   Check if all roles is present, or any of the roles
	 *
	 * @return boolean Whether the/all user(s) with the given ID(s) is/are in the given role
	 * @author Phil Sturgeon
	 **/
	public function hasRole($checkGroup, ?int $id=null, bool $checkAll=false): bool
	{
		$this->triggerEvents('in_role');

		$id || $id = $this->getSessionProperty('id');

		if (! is_array($checkGroup))
		{
			$checkGroup = [$checkGroup];
		}

		if (isset($this->cacheUserInGroup[$id]))
		{
			$rolesArray = $this->cacheUserInGroup[$id];
		}
		else
		{
			$usersGroups = $this->getUserRoles($id);
			$rolesArray = [];
			foreach ($usersGroups as $role)
			{
				$rolesArray[$role->id] = $role->name;
			}
			$this->cacheUserInGroup[$id] = $rolesArray;
		}
		foreach ($checkGroup as $key => $value)
		{
			$roles = (is_numeric($value)) ? array_keys($rolesArray) : $rolesArray;

			/**
			 * if !all (default), in_array
			 * if all, !in_array
			 */
			if (in_array($value, $roles) xor $checkAll)
			{
				/**
				 * if !all (default), true
				 * if all, false
				 */
				return ! $checkAll;
			}
		}

		/**
		 * if !all (default), false
		 * if all, true
		 */
		return $checkAll;
	}

	/**
 * Add to role
 *
 * @param array|integer $roleIds Groups id
 * @param integer       $userId   User id
 *
 * @return integer The number of roles added
 * @author Ben Edmunds
 */
public function addUserToGroup(?array $roleIds = null, ?int $userId = null, bool $append = false ): int
{
	$this->triggerEvents('add_user_to_role');

	// if no id was passed use the current users id
	// TODO need security check to ensure user can add themselves to a role
	$userId || $userId = $this->getSession('id');

	if( !$roleIds )
	{
		return 0;
	}

	if (! is_array($roleIds))
	{
		$roleIds = [$roleIds];
	}

	$rolesUsers = [];

	foreach ($roleIds as $role)
	{
		if( is_object( $role ) && ! is_null( $role->id ) ) {
			// $role is an Group Entity
			$roleId = $role->id;
		} else if ( is_int($role) || is_float($role) || is_string($role) ){
			// $role is just a role id
			$roleId = $role;
		} else {
			// could not determine the type of data for $role silent ignore
			continue;
		}
		// Cast to float to support bigint data type
		$rolesUsers[] = [
			$this->config->join['roles'] => (float)$roleId, // assumed Group exists
			$this->config->join['users']  => (float)$userId, // assumed User exists
		];

		// TODO should this be cached?
	}

	if( ! empty( $rolesUsers ) )
	{
		if( !$append )
		{
			$this->db->table($this->config->tables['roles_users'])->delete(['user_id' => $userId]);
		}
		$this->db->table($this->config->tables['roles_users'])->insertBatch($rolesUsers);
	}

	return count($rolesUsers);
}

/**
 * Remove from role
 *
 * @param array|integer $roleIds Group id
 * @param integer       $userId   User id
 *
 * @return boolean
 * @author Ben Edmunds
 */
public function removeFromGroup($roleIds=0, int $userId=0): bool
{
	$this->triggerEvents('remove_from_role');

	// user id is required
	if (! $userId)
	{
		return false;
	}

	$builder = $this->db->table($this->config->tables['users_roles']);

	// if role id(s) are passed remove user from the role(s)
	if (! empty($roleIds))
	{
		if (! is_array($roleIds))
		{
			$roleIds = [$roleIds];
		}

		foreach ($roleIds as $roleId)
		{
			$builder->delete([$this->config->join['roles'] => (int)$roleId, $this->config->join['users'] => $userId]);
			if (isset($this->cacheUserInGroup[$userId]) && isset($this->cacheUserInGroup[$userId][$roleId]))
			{
				unset($this->cacheUserInGroup[$userId][$roleId]);
			}
		}

		$return = true;
	}
	// otherwise remove user from all roles
	else
	{
		if ($return = $builder->delete([$this->config->join['users'] => $userId]))
		{
			$this->cacheUserInGroup[$userId] = [];
			$return = true;
		}
	}
	return $return;
}

	/**
	 * Update last login
	 *
	 * @param integer $id User id
	 *
	 * @return boolean
	 */
	public function setLoginAttempt(string $identity, string $status = 'failed', int $id = null): bool
	{
		$this->triggerEvents('update_last_login');
		$this->triggerEvents('extra_where');

		// TODO rework this into loginModel
		$this->db->table($this->config->tables['track_login'])->insert([
			'identity' => $identity,
			'user_id' => $id,
			'activity' => $status,
			'created_at' => date('Y-m-d H:i:s'),
		]);
		return $this->db->affectedRows() === 1;
	}

	public function getSession()
	{
		return (object) $this->session->get($this->config->sessionKey);
	}

	public function getSessionProperty( string $key = null )
	{
		$activeUser = $this->getSession();
		return !is_null( $key ) && !is_null( $activeUser ) && isset($activeUser->{$key}) ? $activeUser->{$key} : null;
	}

	/**
	 * Set session
	 *
	 * @param \stdClass $user User
	 *
	 * @return boolean
	 */
	public function setSession(\stdClass $user = null): bool
	{
		$this->triggerEvents('pre_set_session');
		if( $user && isset( $user->id ) ){
			d($user);
			$profile = $this->fillProfile( $user );
			$profile->capabilities = !empty($user->capabilities) ? $user->capabilities : $this->capabilityModel->getCapabilitiesByUser( $user->id );
			$profile->roles = !empty($user->roles) ? $user->roles : $this->getUserRoles( $user->id );
			$this->session->set([$this->config->sessionKey => $profile->toRawArray()]);
		} else {
			$this->session->remove($this->config->sessionKey);
		}

		// Regenerate the session (for security purpose: to avoid session fixation)
		$this->session->regenerate(false);

		$this->triggerEvents('post_set_session');
		return true;
	}


		/**
		* @param integer $id If a user id is not passed the id of the currently logged in user will be used
		*
		* @return \CodeIgniter\Database\ResultInterface
		*/
	public function getUserRoles( int $userId = null )
	{
		// if no id provided use the current session active user_id
		$userId || $userId = $this->getSessionProperty('id');
		return $this->roleModel->getRolesByUserId( $userId );
	}

	/**
	 * Set a user to be remembered
	 *
	 * Implemented as described in
	 * https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
	 *
	 * @param string $identity Identity
	 *
	 * @return boolean
	 * @author Ben Edmunds
	 */
	public function rememberUser(string $identity): bool
	{
		$this->triggerEvents('pre_remember_user');

		if (! $identity)
		{
			return false;
		}

		// Generate random tokens
		$token = $this->generateSelectorValidatorCouple();

		if ($token->validatorHashed)
		{
			$this->db->table($this->config->tables['users'])->update(['remember_selector' => $token->selector,
								  			   'remember_code' => $token->validatorHashed],
											   [$this->config->identity => $identity]);

			if ($this->db->affectedRows() > -1)
			{
				// if the userExpire is set to zero we'll set the expiration two years from now.
				if ( $this->config->userExpire === 0)
				{
					$expire = self::MAX_COOKIE_LIFETIME;
				}
				// otherwise use what is set
				else
				{
					$expire = $this->config->userExpire;
				}

				set_cookie([
					'name'   => $this->config->rememberCookieName,
					'value'  => $token->userCode,
					'expire' => $expire
				]);

				$this->triggerEvents(['post_remember_user', 'remember_user_successful']);
				return true;
			}
		}

		$this->triggerEvents(['post_remember_user', 'remember_user_unsuccessful']);
		return false;
	}

	/**
	 * Login automatically a user with the "Remember me" feature
	 * Implemented as described in
	 * https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
	 *
	 * @return boolean
	 * @author Ben Edmunds
	 */
	public function loginRememberedUser(): bool
	{
		$this->triggerEvents('pre_login_remembered_user');

		// Retrieve token from cookie
		$rememberCookie = get_cookie($this->config->rememberCookieName);
		$token          = $this->retrieveSelectorValidatorCouple($rememberCookie);

		if ($token === false)
		{
			$this->triggerEvents(['post_login_remembered_user', 'post_login_remembered_user_unsuccessful']);
			return false;
		}

		// get the user with the selector
		$this->triggerEvents('extra_where');
		$query = $this->db->table($this->config->tables['users'])
						  ->select($this->config->identity . ', id, email, remember_code, last_login')
						  ->where('remember_selector', $token->selector)
						  ->where('active', 1)
						  ->limit(1)
						  ->get();

		// Check that we got the user
		if ($query->numRows() === 1)
		{
			// Retrieve the information
			$user = $query->row();

			// Check the code against the validator
			$identity = $user->{$this->config->identity};
			if ($this->verifyPassword($token->validator, $user->remember_code, $identity))
			{
				$this->trackAttempts($user->id);

				$this->setSession($user);

				$this->clearForgottenPasswordCode($identity);

				// extend the users cookies if the option is enabled
				if ($this->config->userExtendonLogin)
				{
					$this->rememberUser($identity);
				}

				// Regenerate the session (for security purpose: to avoid session fixation)
				$this->session->regenerate(false);

				$this->triggerEvents(['post_login_remembered_user', 'post_login_remembered_user_successful']);
				return true;
			}
		}
		delete_cookie($this->config->rememberCookieName);

		$this->triggerEvents(['post_login_remembered_user', 'post_login_remembered_user_unsuccessful']);
		return false;
	}
	/**
	 * Get user profile details
	 *
	 * @return integer|null The user's ID from the session user data or NULL if not found
	 * @author jrmadsen67
	 **/
	public function getUserProfile( string $userId = null, string $identifier = 'guid' )
	{
		// session fallback if userId not provided
		if( is_null($userId) )
		{
			$user = $this->getSession();
		}
		else if( $identifier == 'guid' )
		{
			$user = $this->userModel->getUserByGuid( $userId );
		}
		else if ( $identifier == 'id' )
		{
			$user = $this->userModel->asObject()->find( $userId );
		}

		if( !$user )
		{
			return null;
		}

		$profile = $this->fillProfile( $user );
		$profile->capabilities = !empty($user->capabilities) ? $user->capabilities : $this->capabilityModel->getCapabilitiesByUser( $user->id );
		$profile->roles = !empty($user->roles) ? $user->roles : $this->getUserRoles( $user->id );
		return (object) $profile->toRawArray();

	}

	private function fillProfile( object $user = null) : \AtomicAuth\Entities\Profile
	{
		// build out profile for roles and capabilities
		$profile = new \AtomicAuth\Entities\Profile();
		// TODO cleaner way to fill?
		// $profile->fill( (array) $user);
		$profile->identity = $user->{$this->config->identity};
		$profile->last_check = time();
		$profile->email = $user->email;
		$profile->id = $user->id;
		$profile->guid = $user->guid;
		$profile->status = $user->status;
		return $profile;
	}
	/**
	 * Can a user do ""
	 * @param $key string or array can accept multiple keys to filter against
	 * if user has any matches will allow user "can"
	 */
	public function userCan( $key = null, int $userId = null, bool $OR = FALSE ) : bool
	{
		if(empty($key))
		{
			return false;
		}
		if( is_null( $userId ) )
		{
			$capabilities = $this->getSessionProperty( 'capabilities' );
		}
		else
		{
			$capabilities = $this->capabilityModel->getCapabilitiesByUser( $user->id );
		}
		if( !empty($capabilities) )
		{
			$capabilities = array_column($capabilities, 'name');
			if($OR && is_array($key))
			{
				// Check if ALL of the needles exist
				return empty(array_diff($key, $capabilities));
			}
			else if(!$OR && is_array($key))
			{
				// Check if ANY of the needles exist
				return !empty(array_intersect($key, $capabilities));
			}
			return in_array( $key, $capabilities );
		}
		return false;
	}

	/**
	 * Create a role
	 *
	 * @param string $roleName        Group name
	 * @param string $roleDescription Group description
	 * @param array  $additionalData   Additional data
	 *
	 * @return integer|boolean The ID of the inserted role, or false on failure
	 * @author aditya menon
	 */
	public function createGroup(string $roleName='', string $roleDescription='', array $additionalData=[])
	{
		// bail if the role name was not passed
		if (! $roleName)
		{
			$this->setError('AtomicAuth.roleName_required');
			return false;
		}

		// bail if the role name already exists
		$existingGroup = $this->db->table($this->config->tables['roles'])->where(['name' => $roleName])->countAllResults();
		if ($existingGroup !== 0)
		{
			$this->setError('AtomicAuth.role_already_exists');
			return false;
		}

		$data = [
			'name'        => $roleName,
			'description' => $roleDescription,
		];

		// filter out any data passed that doesnt have a matching column in the roles table
		// and merge the set role data and the additional data
		if (! empty($additionalData))
		{
			$data = array_merge($this->filterData($this->config->tables['roles'], $additionalData), $data);
		}

		$this->triggerEvents('extra_role_set');

		// insert the new role
		$this->db->table($this->config->tables['roles'])->insert($data);
		$roleId = $this->db->insertId($this->config->tables['roles'] . '_id_seq');

		// report success
		$this->setMessage('AtomicAuth.role_creation_successful');
		// return the brand new role id
		return $roleId;
	}

	/**
	 * Update role
	 *
	 * @param integer $roleId        Group id
	 * @param string  $roleName      Group name
	 * @param array   $additionalData Additional datas
	 *
	 * @return boolean
	 * @author aditya menon
	 */
	public function updateGroup(int $roleId, string $roleName='', array $additionalData=[]): bool
	{
		if (! $roleId)
		{
			return false;
		}

		$data = [];

		if (! empty($roleName))
		{
			// we are changing the name, so do some checks

			// bail if the role name already exists
			$existingGroup = $this->db->table($this->config->tables['roles'])->getWhere(['name' => $roleName])->getRow();
			if (isset($existingGroup->id) && (int)$existingGroup->id !== $roleId)
			{
				$this->setError('AtomicAuth.role_already_exists');
				return false;
			}

			$data['name'] = $roleName;
		}

		// restrict change of name of the admin role
		$role = $this->db->table($this->config->tables['roles'])->getWhere(['id' => $roleId])->getRow();
		if ($this->config->adminRole === $role->name && $roleName !== $role->name)
		{
			$this->setError('AtomicAuth.roleName_admin_not_alter');
			return false;
		}

		// filter out any data passed that doesnt have a matching column in the roles table
		// and merge the set role data and the additional data
		if (! empty($additionalData))
		{
			$data = array_merge($this->filterData($this->config->tables['roles'], $additionalData), $data);
		}

		$this->db->table($this->config->tables['roles'])->update($data, ['id' => $roleId]);

		$this->setMessage('AtomicAuth.role_update_successful');

		return true;
	}

	/**
	 * Remove a role.
	 *
	 * @param integer $roleId Group id
	 *
	 * @return boolean
	 * @author aditya menon
	 */
	public function deleteGroup(int $roleId): bool
	{
		// bail if mandatory param not set
		if (! $roleId || empty($roleId))
		{
			return false;
		}
		$role = $this->role($roleId)->row();
		if ($role->name === $this->config->adminRole)
		{
			$this->triggerEvents(['post_delete_role', 'post_delete_role_notallowed']);
			$this->setError('AtomicAuth.role_delete_notallowed');
			return false;
		}

		$this->triggerEvents('pre_delete_role');

		$this->db->transBegin();

		// remove all users from this role
		$this->db->table($this->config->tables['users_roles'])->delete([$this->config->join['roles'] => $roleId]);
		// remove the role itself
		$this->db->table($this->config->tables['roles'])->delete(['id' => $roleId]);

		if ($this->db->transStatus() === false)
		{
			$this->db->transRollback();
			$this->triggerEvents(['post_delete_role', 'post_delete_role_unsuccessful']);
			$this->setError('AtomicAuth.role_delete_unsuccessful');
			return false;
		}

		$this->db->transCommit();

		$this->triggerEvents(['post_delete_role', 'post_delete_role_successful']);
		$this->setMessage('role_delete_successful');
		return true;
	}

	/**
	 * expose the Auth Group Model
	 *
	 * @return string
	 * @author Timothy Wood
	 */
	public function roleModel() //: class
	{
		return $this->roleModel;
	}

	/**
	 * expose the Auth User Model
	 *
	 * @return string
	 * @author Timothy Wood
	 */
	public function userModel() //: class
	{
		return $this->userModel;
	}

	/**
	 * Set a single or multiple functions to be called when trigged by triggerEvents().
	 *
	 * @param string $event     Event
	 * @param string $name      Name
	 * @param string $class     Class
	 * @param string $method    Method
	 * @param array  $arguments Arguments
	 *
	 * @return self
	 */
	public function setHook(string $event, string $name, string $class, string $method, array $arguments=[]): self
	{
		$this->authHooks->{$event}[$name]            = new stdClass;
		$this->authHooks->{$event}[$name]->class     = $class;
		$this->authHooks->{$event}[$name]->method    = $method;
		$this->authHooks->{$event}[$name]->arguments = $arguments;
		return $this;
	}

	/**
	 * Remove hook
	 *
	 * @param string $event Event
	 * @param string $name  Name
	 *
	 * @return void
	 */
	public function removeHook(string $event, string $name): void
	{
		if (isset($this->authHooks->{$event}[$name]))
		{
			unset($this->authHooks->{$event}[$name]);
		}
	}

	/**
	 * Remove hooks
	 *
	 * @param string $event Event
	 *
	 * @return void
	 */
	public function removeHooks(string $event): void
	{
		if (isset($this->authHooks->$event))
		{
			unset($this->authHooks->$event);
		}
	}

	/**
	 * Call hook
	 *
	 * @param string $event Event
	 * @param string $name  Name
	 *
	 * @return false|mixed
	 */
	protected function callHook(string $event, string $name)
	{
		if (isset($this->authHooks->{$event}[$name]) && method_exists($this->authHooks->{$event}[$name]->class, $this->authHooks->{$event}[$name]->method))
		{
			$hook = $this->authHooks->{$event}[$name];

			return call_user_func_array([$hook->class, $hook->method], $hook->arguments);
		}

		return false;
	}

	/**
	 * Call Additional functions to run that were registered with setHook().
	 *
	 * @param string|array $events Event(s)
	 *
	 * @return void
	 */
	public function triggerEvents($events): void
	{
		if (is_array($events) && ! empty($events))
		{
			foreach ($events as $event)
			{
				$this->triggerEvents($event);
			}
		}
		else
		{
			if (isset($this->authHooks->$events) && ! empty($this->authHooks->$events))
			{
				foreach ($this->authHooks->$events as $name => $hook)
				{
					$this->callHook($events, $name);
				}
			}
		}
	}

	/**
	 * Set the message templates
	 *
	 * @param string $single Template for single message
	 * @param string $list	 Template for list messages
	 *
	 * @return true
	 * @author Ben Edmunds
	 */
	public function setMessageTemplate(string $single='', string $list=''): bool
	{
		if (! empty($single))
		{
			$this->config->templates['messages']['single'] = $single;
		}

		if (! empty($list))
		{
			$this->config->templates['messages']['list'] = $list;
		}

		return true;
	}

	/**
	 * Set a message
	 *
	 * @param string $message The message
	 *
	 * @return string The given message
	 * @author Ben Edmunds
	 */
	public function setMessage(string $message): string
	{
		$this->messages[] = $message;

		return $message;
	}

	/**
	 * Get the messages
	 *
	 * @return string
	 * @author Ben Edmunds
	 */
	public function messages(): string
	{
		if (empty($this->messages))
		{
			return '';
		}

		$messageLang = [];
		foreach ($this->messages as $message)
		{
			$messageLang[] = lang($message) !== $message ? lang($message) : '##' . $message . '##';
		}
		return view($this->config->templates['messages']['list'], ['messages' => $messageLang]);
	}

	/**
	 * Get the messages as an array
	 *
	 * @param boolean $langify Translate messages ?
	 *
	 * @return array
	 * @author Raul Baldner Junior
	 */
	public function messagesArray(bool $langify=true): array
	{
		if ($langify)
		{
			$output = [];
			foreach ($this->messages as $message)
			{
				$messageLang = lang($message) !== $message ? lang($message) : '##' . $message . '##';
				$output[]    = view($this->config->templates['messages']['single'], ['message' => $messageLang]);
			}
			return $output;
		}
		else
		{
			return $this->messages;
		}
	}

	/**
	 * Clear messages
	 *
	 * @return true
	 * @author Ben Edmunds
	 */
	public function clearMessages()
	{
		$this->messages = [];

		return true;
	}

	/**
	 * Set an error message
	 *
	 * @param string $error The error to set
	 *
	 * @return string The given error
	 * @author Ben Edmunds
	 */
	public function setError(string $error): string
	{
		$this->errors[] = $error;

		return $error;
	}

	/**
	 * Get the error message
	 *
	 * @param string $template Template @see https://codeigniter4.github.io/CodeIgniter4/libraries/validation.html#configuration
	 *
	 * @return string
	 * @author Ben Edmunds
	 */
	public function errors(string $template='list'): string
	{
		if (! array_key_exists($template, config('Validation')->templates))
		{
			throw new \CodeIgniter\Exceptions\ConfigException(lang('Validation.invalidTemplate', [$template]));
		}

		$errors = [];
		foreach ($this->errors as $error)
		{
			$errors[] = lang($error) !== $error ? lang($error) : '##' . $error . '##';
		}

		return view(config('Validation')->templates[$template], ['errors' => $errors]);
	}

	/**
	 * Get the error messages as an array
	 *
	 * @param boolean $langify Langify errors ?
	 *
	 * @return array
	 * @author Raul Baldner Junior
	 *
	 * @deprecated No longer used by internal code and not recommended.
	 */
	public function errorsArray(bool $langify = true): array
	{
		if ($langify)
		{
			$output = [];
			foreach ($this->errors as $error)
			{
				$output[] = lang($error) !== $error ? lang($error) : '##' . $error . '##';
			}
			return $output;
		}
		else
		{
			return $this->errors;
		}
	}

	/**
	 * Get the error messages as an array
	 *
	 * @param boolean $langify Langify errors ?
	 *
	 * @return array
	 * @author Benoit VRIGNAUD
	 */
	public function getErrors(bool $langify=true): array
	{
		if ($langify)
		{
			$output = [];
			foreach ($this->errors as $error)
			{
				$output[] = lang($error);
			}
			return $output;
		}
		else
		{
			return $this->errors;
		}
	}

	/**
	 * Clear Errors
	 *
	 * @return true
	 * @author Ben Edmunds
	 */
	public function clearErrors(): boolean
	{
		$this->errors = [];

		return true;
	}



		  /**
			 * Identity Exists : Check to see if the identity is already registered
			 *
			 * @param string $identity Identity
			 *
			 * @return boolean
			 * @author Mathew
			 */
			public function identityExists(string $identity=''): bool
			{
				$this->triggerEvents('identity_exists');

				if (empty($identity))
				{
					return false;
				}
				// check against identity
				return $this->userModel->where($this->config->identity, $identity)->limit(1)->countAllResults() > 0;
			}


			public function roleExists( string $role = null, string $lookupColumn = 'guid'): bool
			{
				$this->triggerEvents('role_exists');

				if (empty($role))
				{
					return false;
				}
// dd($this->roleModel->where('name', $role)->limit(1)->first());
				// TRUE: we expect that the size of found role to be greater than 0
				return ($this->roleModel->where($lookupColumn, $role)->limit(1)->findAll() > 0);
			}

			public function rolesExist(array $roles = []): bool
			{
				$this->triggerEvents('roles_exists');

				if (empty($roles))
				{
					return false;
				} else if( ! is_array( $roles ) )
				{
					$roles = [ $roles ];
				}

				// TRUE: we expect that the size of provided $roles should be the same as those found
				return ($this->roleModel->whereIn('name', $roles)->countAllResults() == count( $roles ));
			}

	/**
	 * Internal function to set a password in the database
	 *
	 * @param string $identity Identity
	 * @param string $password Password
	 *
	 * @return boolean
	 */
	protected function setPasswordDb(string $identity, string $password): bool
	{
		$hash = $this->hashPassword($password, $identity);

		if ($hash === false)
		{
			return false;
		}

		// When setting a new password, invalidate any other token
		$data = [
			'password'                => $hash,
			'remember_code'           => null,
			'forgotten_password_code' => null,
			'forgotten_password_time' => null,
		];

		$this->triggerEvents('extra_where');

		$this->db->table($this->config->tables['users'])->update($data, [$this->config->identity => $identity]);

		return $this->db->affectedRows() === 1;
	}

	/**
	 * Filter data
	 *
	 * @param string $table Table
	 * @param array  $data  Data
	 *
	 * @return array
	 */
	protected function filterData(string $table, $data): array
	{
		$filteredData = [];
		$columns = $this->db->getFieldNames($table);

		if (is_array($data))
		{
			foreach ($columns as $column)
			{
				if (array_key_exists($column, $data))
				{
					$filteredData[$column] = $data[$column];
				}
			}
		}

		return $filteredData;
	}

	/**
	 * Generate a random token
	 * Inspired from http://php.net/manual/en/function.random-bytes.php#118932
	 *
	 * @param integer $resultLength Result lenght
	 *
	 * @return string
	 */
	protected function randomToken(int $resultLength=32): string
	{
		if ($resultLength <= 8)
		{
			$resultLength = 32;
		}

		// Try random_bytes: PHP 7
		if (function_exists('random_bytes'))
		{
			return bin2hex(random_bytes($resultLength / 2));
		}

		// No luck!
		throw new \Exception('Unable to generate a random token');
	}

	/**
	 * Retrieve hash parameter according to options
	 *
	 * @param string $identity Identity
	 *
	 * @return array|boolean
	 */
	protected function getHashParameters(string $identity='')
	{
		// Check if user is administrator or not
		$isAdmin = false;
		if ($identity)
		{
			$userId = $this->getUserIdFromIdentity($identity);
			if ($userId && $this->inGroup($this->config->adminRole, $userId))
			{
				$isAdmin = true;
			}
		}

		$params = false;
		switch ($this->config->hashMethod)
		{
			case 'bcrypt':
				$params = [
					'cost' => $isAdmin ? $this->config->bcryptAdminCost
										: $this->config->bcryptDefaultCost
				];
				break;

			case 'argon2':
				$params = $isAdmin ? $this->config->argon2AdminParams
									: $this->config->argon2DefaultParams;
				break;

			default:
				// Do nothing
		}

		return $params;
	}

	/**
	 * Retrieve hash algorithm according to options
	 *
	 * @return string|boolean
	 */
	protected function getHashAlgo()
	{
		$algo = false;
		switch ($this->config->hashMethod)
		{
			case 'bcrypt':
				$algo = PASSWORD_BCRYPT;
				break;

			case 'argon2':
				$algo = PASSWORD_ARGON2I;
				break;

			default:
				// Do nothing
		}

		return $algo;
	}

	/**
	 * Generate a random selector/validator couple
	 * This is a user code
	 *
	 * @param integer $selectorSize  Size of the selector token
	 * @param integer $validatorSize Size of the validator token
	 *
	 * @return \stdClass
	 *          ->selector			simple token to retrieve the user (to store in DB)
	 *          ->validatorHashed	token (hashed) to validate the user (to store in DB)
	 *          ->user_code			code to be used user-side (in cookie or URL)
	 */
	protected function generateSelectorValidatorCouple(int $selectorSize=40, int $validatorSize=128): \stdClass
	{
		// The selector is a simple token to retrieve the user
		$selector = $this->randomToken($selectorSize);

		// The validator will strictly validate the user and should be more complex
		$validator = $this->randomToken($validatorSize);

		// The validator is hashed for storing in DB (avoid session stealing in case of DB leaked)
		$validatorHashed = $this->hashPassword($validator);

		// The code to be used user-side
		$userCode = $selector . '.' . $validator;

		return (object) [
			'selector'        => $selector,
			'validatorHashed' => $validatorHashed,
			'userCode'        => $userCode,
		];
	}

	/**
	 * Retrieve remember cookie info
	 *
	 * @param string $userCode A user code of the form "selector.validator"
	 *
	 * @return false|stdCalss
	 *          ->selector		simple token to retrieve the user in DB
	 *          ->validator		token to validate the user (check against hashed value in DB)
	 */
	protected function retrieveSelectorValidatorCouple(string $userCode)
	{
		// Check code
		if ($userCode)
		{
			$tokens = explode('.', $userCode);

			// Check tokens
			if (count($tokens) === 2)
			{
				return (object) [
					'selector'  => $tokens[0],
					'validator' => $tokens[1],
				];
			}
		}

		return false;
	}

}
