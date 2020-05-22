<?php
namespace AtomicAuth\Libraries;

/**
 * Name:    Atomic Auth
 *
 * Created:  10.01.2009
 *
 * Description:  Modified auth system based on redux_auth with extensive customization.
 *               This is basically what Redux Auth 2 should be.
 * Original Author name has been kept but that does not mean that the method has not been modified.
 *
 * Requirements: PHP7.2 or above
 *
 * @package    CodeIgniter-Atomic-Auth
 * @author     Ben Edmunds <ben.edmunds@gmail.com>
 * @author     Phil Sturgeon
 * @author     Benoit VRIGNAUD <benoit.vrignaud@zaclys.net>
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       http://github.com/benedmunds/CodeIgniter-Ion-Auth
 * @filesource
 */

/**
 * This class is the AtomicAuth library.
 */
class AtomicAuth
{
	/**
	 * Configuration
	 *
	 * @var \AtomicAuth\Config\AtomicAuth
	 */
	protected $config;

	/**
	 * AtomicAuth model
	 *
	 * @var \AtomicAuth\Models\AtomicAuthModel
	 */
	protected $atomicAuthModel;

	/**
	 * Email class
	 *
	 * @var \CodeIgniter\Email\Email
	 */
	protected $email;

	/**
	 * Max cookie lifetime constant
	 */
	const MAX_COOKIE_LIFETIME = 63072000; // 2 years = 60*60*24*365*2 = 63072000 seconds;



	/**
	 * __construct
	 *
	 * @author Ben
	 */
	public function __construct()
	{
		// Check compat first
		$this->checkCompatibility();

		$this->config = config('AtomicAuth');
		// $config = config('AtomicAuth\\Config\\AtomicAuth');

		$this->email = \Config\Services::email();
		helper('cookie');

		$this->session = session();

		$this->atomicAuthModel = new \AtomicAuth\Models\AtomicAuthModel();

		$emailConfig = $this->config->emailConfig;

		if ($this->config->useCiEmail && isset($emailConfig) && is_array($emailConfig))
		{
			$this->email->initialize($emailConfig);
		}

		$this->atomicAuthModel->triggerEvents('library_constructor');
	}

	/**
	 * __call
	 *
	 * Acts as a simple way to call model methods without loads of stupid alias'
	 *
	 * @param string $method    Method to call
	 * @param array  $arguments Method arguments
	 *
	 * @return mixed
	 * @throws Exception When $method is undefined.
	 */
			// TODO update this to reflect new models
	public function __call(string $method, array $arguments)
	{
		if (! method_exists( $this->atomicAuthModel, $method))
		{
			throw new \Exception('Undefined method Atomic_auth::' . $method . '() called');
		}
		if ($method === 'create_user')
		{
			return call_user_func_array([$this, 'register'], $arguments);
		}
		if ($method === 'update_user')
		{
			return call_user_func_array([$this, 'update'], $arguments);
		}
		return call_user_func_array([$this->atomicAuthModel, $method], $arguments);
	}

	/**
	 * Forgotten password feature
	 *
	 * @param string $identity Identity
	 *
	 * @return array|boolean
	 * @author Mathew
	 */
	public function forgottenPassword(string $identity)
	{
		// Retrieve user information
		$user = $this->where($this->atomicAuthModel->identityColumn, $identity)
					 ->where('active', 1)
					 ->users()->row();

		if ($user)
		{
			// Generate code
			$code = $this->atomicAuthModel->forgottenPassword($identity);

			if ($code)
			{
				$data = [
					'identity'              => $identity,
					'forgottenPasswordCode' => $code,
				];

				if (! $this->config->useCiEmail)
				{
					$this->setMessage('AtomicAuth.forgot_password_successful');
					return $data;
				}
				else
				{
					$message = view($this->config->emailTemplates . $this->config->emailForgotPassword, $data);
					$this->email->clear();
					$this->email->setFrom($this->config->adminEmail, $this->config->siteTitle);
					$this->email->setTo($user->email);
					$this->email->setSubject($this->config->siteTitle . ' - ' . lang('AtomicAuth.email_forgotten_password_subject'));
					$this->email->setMessage($message);
					if ($this->email->send())
					{
						$this->setMessage('AtomicAuth.forgot_password_successful');
						return true;
					}
				}
			}
		}

		$this->setError('AtomicAuth.forgot_password_unsuccessful');
		return false;
	}

	/**
	 * Forgotten password check
	 *
	 * @param string $code Code
	 *
	 * @return object|boolean
	 * @author Michael
	 */
	public function forgottenPasswordCheck(string $code)
	{
		$user = $this->atomicAuthModel->getUserByForgottenPasswordCode($code);

		if (! is_object($user))
		{
			$this->setError('AtomicAuth.password_change_unsuccessful');
			return false;
		}
		else
		{
			if ($this->config->forgotPasswordExpiration > 0)
			{
				//Make sure it isn't expired
				$expiration = $this->config->forgotPasswordExpiration;
				if (time() - $user->forgotten_password_time > $expiration)
				{
					//it has expired
					$identity = $user->{$this->config->identity};
					$this->atomicAuthModel->clearForgottenPasswordCode($identity);
					$this->setError('AtomicAuth.password_change_unsuccessful');
					return false;
				}
			}
			return $user;
		}
	}

	/**
	 * Register
	 *
	 * @param string $identity       Identity
	 * @param string $password       Password
	 * @param string $email          Email
	 * @param array  $additionalData Additional data
	 * @param array  $groupIds       Groups id
	 *
	 * @return integer|array|boolean The new user's ID if e-mail activation is disabled or Atomic-Auth e-mail activation
	 *                               was completed;
	 *                               or an array of activation details if CI e-mail validation is enabled; or false
	 *                               if the operation failed.
	 * @author Mathew
	 */
	public function register(string $identity, string $password, string $email, array $userMeta = [], array $groups = [])
	{
		// $this->atomicAuthModel->triggerEvents('pre_account_creation');

		// check if user exists
		if ($this->identityExists($identity))
		{
			$this->setError('AtomicAuth.account_creation_duplicate_identity');
			return false;
		}

		// check default group exists for failback
		if (! $this->config->defaultGroup && empty($groups))
		{
			$this->setError('AtomicAuth.account_creation_missing_defaultGroup');
			return false;
		}

		// check if the default group exists in database
		if ( empty($groups) && ! $this->atomicAuthModel->groupExists($this->config->defaultGroup))
		{
			$this->setError('AtomicAuth.account_creation_invalid_defaultGroup');
			return false;
		}

		if( ! empty($groups) )
		{
			// TODO check for if specified group(s) exist in db too
		}
		else
		{
			// no groups supplied, use a default group to associate to user
			$groups[] = $this->atomicAuthModel->groupModel()->getGroupByGuid( $this->config->defaultGroup );
		}

		// setup user to model
				$user = (object) $this->atomicAuthModel->createUser($identity, $password, $email, $userMeta)->toArray();

				if ( ! $user->id )
				{
					$this->setError('AtomicAuth.account_creation_unsuccessful');
					return false;
				}

				// add user to group association
				$this->atomicAuthModel->addUserToGroup($groups, $user->id);

				$this->setMessage('AtomicAuth.account_creation_successful');

				return $user;
	}

	/**
	 * Logout
	 *
	 * @return true
	 * @author Mathew
	 */
	public function logout(): bool
	{
		$this->atomicAuthModel->triggerEvents('logout');

		$activeUser = $this->session->get('activeUser');
		if( $activeUser ) {
			// TODO make these methods work
			// $this->atomicAuthModel->clearForgottenPasswordCode($activeUser['user_id']);
			// $this->atomicAuthModel->resetRememberToken($activeUser['user_id']);
		}

		$this->setSession(null);

		// delete the remember me cookies if they exist
		delete_cookie($this->config->rememberCookieName);

		// Destroy the session
		$this->session->destroy();

		// Recreate the session
		session_start();
		session_regenerate_id(true);

		$this->setMessage('AtomicAuth.logout_successful');
		return true;
	}



	/**
	 * Auto logs-in the user if they are remembered
	 *
	 * @author Mathew
	 *
	 * @return boolean Whether the user is logged in
	 */
	public function loggedIn(): bool
	{
		$this->atomicAuthModel->triggerEvents('logged_in');

		$recheck = $this->atomicAuthModel->recheckSession();

		// auto-login the user if they are remembered
		// if (! $recheck && get_cookie($this->config->rememberCookieName))
		// {
		// 	$recheck = $this->atomicAuthModel->loginRememberedUser();
		// }

		return $recheck;
	}

	/**
	 * Get user id
	 *
	 * @return integer|null The user's ID from the session user data or NULL if not found
	 * @author jrmadsen67
	 **/
	public function getUserId()
	{
		$userId = $this->session->get('user_id');
		if (! empty($userId) )
		{
			return $userId;
		}
		return null;
	}

	/**
	 * Check to see if the currently logged in user is an admin.
	 *
	 * @param integer $id User id
	 *
	 * @return boolean Whether the user is an administrator
	 * @author Ben Edmunds
	 */
	public function isAdmin(int $id=0): bool
	{
		$this->atomicAuthModel->triggerEvents('is_admin');

		// TODO fix this!!!!
		return true;

		$adminGroup = $this->config->adminGroup;

		return $this->atomicAuthModel->inGroup($adminGroup, $id);
	}

	/**
	 * Check the compatibility with the server
	 *
	 * Script will die in case of error
	 *
	 * @return void
	 */
	protected function checkCompatibility()
	{
		// I think we can remove this method

		/*
		// PHP password_* function sanity check
		if (!function_exists('password_hash') || !function_exists('password_verify'))
		{
			show_error("PHP function password_hash or password_verify not found. " .
				"Are you using CI 2 and PHP < 5.5? " .
				"Please upgrade to CI 3, or PHP >= 5.5 " .
				"or use password_compat (https://github.com/ircmaxell/password_compat).");
		}
		*/

		/*
		// Compatibility check for CSPRNG
		// See functions used in Atomic_auth_model::randomToken()
		if (!function_exists('random_bytes') && !function_exists('mcrypt_create_iv') && !function_exists('openssl_random_pseudo_bytes'))
		{
			show_error("No CSPRNG functions to generate random enough token. " .
				"Please update to PHP 7 or use random_compat (https://github.com/paragonie/random_compat).");
		}
		*/
	}




}
