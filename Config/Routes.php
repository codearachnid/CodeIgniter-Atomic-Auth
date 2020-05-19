<?php namespace Config;

$routes->group('auth', ['namespace' => 'AtomicAuth\Controllers'], function ($routes) {

  // ... generic auth routes
  $routes->add('/', 'Auth::index');
  $routes->add('login', 'Auth::login');
  $routes->add('logout', 'Auth::logout');
  $routes->add('forgot', 'Auth::forgot_password');
  // ...

  // ... user centric
  $routes->put('create', 'User::create');
  $routes->patch('edit/(:hash)', 'User::edit/$1');
  $routes->add('activate/(:hash)', 'User::activate/$1');
  // $routes->get('activate/(:num)/(:hash)', 'Auth::activate/$1/$2');
  $routes->delete('suspend/(:hash)', 'User::suspend/$1');
  // ...

  // ... group/roles
  $routes->add('group/create', 'Group::create');
  $routes->patch('group/edit/(:hash)', 'Group::edit/$1');
  $routes->delete('suspend/(:hash)', 'Group::suspend/$1');
  // ...

  // ... be careful leaving these in if you aren't sure what they do
  $routes->add('admin', 'Admin::go_away');
  $routes->add('admin/generate', 'Admin::salt');
  $routes->add('admin/generate/(:num)', 'Admin::salt/$1');
  $routes->add('admin/install?(:hash)', 'Admin::install/$1');
  $routes->add('admin/uninstall?(:hash)', 'Admin::uninstall/$1');
  // ...

});
