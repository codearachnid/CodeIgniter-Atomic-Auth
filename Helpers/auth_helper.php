<?php // TODO figure out namespace AtomicAuth\Helpers;
/**
 * CodeIgniter Atomic Auth Helpers
 *
 * @package CodeIgniter
 */

if(! function_exists('loggedIn'))
{
	function loggedIn() : bool
	{
		$atomicAuth = new \AtomicAuth\Libraries\AtomicAuth();
		return $atomicAuth->loggedIn();
	}
}
//
// if (! function_exists('isAdmin'))
// {
// 	/**
// 	 * Searches an array through dot syntax. Supports
// 	 * wildcard searches, like foo.*.bar
// 	 *
// 	 * @param string $index
// 	 * @param array  $array
// 	 *
// 	 * @return mixed|null
// 	 */
// 	// TODO determine if we should allow config/model to pass through
// 	// function isAdmin($userOrId, \Config\App $altConfig = null) : bool
// 	function isAdmin($userOrId = null) : bool
// 	{
// 		$isAdmin = false;
// 		// use alternate config if provided, else default one
// 		// $config = $altConfig ?? config(\Config\App::class);
// 		$config = config('AtomicAuth');
// 		$this->atomicAuthModel = new \AtomicAuth\Models\AtomicAuthModel();
//
// 			// convert segment array to string
// 			if (is_array($uri))
// 			{
// 				$uri = implode('/', $uri);
// 			}
//
//
//
//
// 		return (bool) $isAdmin;
// 	}
// }
if(! function_exists('isAdmin'))
{
	function isAdmin() : bool
	{
		$atomicAuth = new \AtomicAuth\Libraries\AtomicAuth();
		return $atomicAuth->isAdmin();
	}
}
if(! function_exists('isDefault'))
{
	function isDefault() : bool
	{
		$atomicAuth = new \AtomicAuth\Libraries\AtomicAuth();
		return $atomicAuth->isDefault();
	}
}
if(! function_exists('getUserProfile'))
{
	function getUserProfile() : bool
	{
		$atomicAuth = new \AtomicAuth\Libraries\AtomicAuth();
		return $atomicAuth->getUserProfile();
	}
}
if(! function_exists('getUserId'))
{
	function getUserId() : bool
	{
		$atomicAuth = new \AtomicAuth\Libraries\AtomicAuth();
		return $atomicAuth->getUserProfile('user_id');
	}
}
