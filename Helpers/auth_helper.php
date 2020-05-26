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
if(! function_exists('getUserProfile'))
{
	function getUserProfile()
	{
		$atomicAuth = new \AtomicAuth\Libraries\AtomicAuth();
		return $atomicAuth->getUserProfile();
	}
}
if(! function_exists('getUserId'))
{
	function getUserId() : int
	{
		$atomicAuth = new \AtomicAuth\Libraries\AtomicAuth();
		return $atomicAuth->getUserProfile('user_id');
	}
}
