<?php // TODO figure out namespace AtomicAuth\Helpers;
/**
 * CodeIgniter Atomic Auth Helpers
 *
 * @package CodeIgniter
 */
if(! function_exists('getAuthLink'))
{
	// TODO build out configurable routes and handler
	function getAuthLink(string $link = 'login')
	{
		return $link;
	}
}
if(! function_exists('loggedIn'))
{
	function loggedIn() : bool
	{
		$atomicAuth = new \AtomicAuth\Libraries\AtomicAuth();
		return $atomicAuth->loggedIn();
	}
}
if(! function_exists('userCan'))
{
	function userCan( string $key = null ) : bool
	{
		if( is_null($key) )
		{
			return false;
		}
		$atomicAuth = new \AtomicAuth\Libraries\AtomicAuth();
		return $atomicAuth->userCan( $key );
	}
}
if(! function_exists('userInGroup'))
{
	function userInGroup( string $key = null ) : bool
	{
		if( is_null($key) )
		{
			return false;
		}
	}
}
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
if(! function_exists('getUserIdentity'))
{
	function getUserIdentity( $user = null )
	{
		$identity = config('AtomicAuth')->identity;
		if( is_null($user) )
		{
			$atomicAuth = new \AtomicAuth\Libraries\AtomicAuth();
			return $atomicAuth->getSessionProperty($identity);
		}
		else if (isset($user->{$identity}))
		{
			return $user->{$identity};
		}
		else {
			return null;
		}
	}
}
// if(! function_exists('getUserProfile'))
// {
// 	function getUserProfile()
// 	{
// 		$atomicAuth = new \AtomicAuth\Libraries\AtomicAuth();
// 		return $atomicAuth->getUserProfile();
// 	}
// }
// if(! function_exists('getUserId'))
// {
// 	function getUserId() : int
// 	{
// 		$atomicAuth = new \AtomicAuth\Libraries\AtomicAuth();
// 		return $atomicAuth->getUserProfile('id');
// 	}
// }
