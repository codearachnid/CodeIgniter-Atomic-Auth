<?php namespace Config;

$routes->group('auth', ['namespace' => 'AtomicAuth\Controllers'], function ($routes) {
  $routes->get('/', 'Auth::index');
  $routes->add('login', 'Auth::login');
  $routes->get('logout', 'Auth::logout');
  $routes->add('forgot', 'Auth::forgot_password');
  $routes->add('create_user', 'Auth::create_user');
  $routes->add('edit_user/(:num)', 'Auth::edit_user/$1');
  $routes->add('create_group', 'Auth::create_group');
  $routes->get('activate/(:num)', 'Auth::activate/$1');
  $routes->get('activate/(:num)/(:hash)', 'Auth::activate/$1/$2');
  $routes->add('deactivate/(:num)', 'Auth::deactivate/$1');

  // ... be careful leaving these in if you aren't sure what they do
  $routes->add('admin', 'Admin::go_away');
  $routes->add('admin/generate', 'Admin::salt');
  $routes->add('admin/generate/(:num)', 'Admin::salt/$1');
  $routes->add('admin/install/(:hash)', 'Admin::install/$1');
  $routes->add('admin/uninstall/(:hash)', 'Admin::uninstall/$1');
  // ...
  
});
