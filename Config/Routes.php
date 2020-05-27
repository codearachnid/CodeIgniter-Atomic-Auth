<?php namespace Config;

$routes->group('auth', ['namespace' => 'AtomicAuth\Controllers'], function ($routes) {

  // ... generic auth routes
  $routes->add('/', 'Auth::index');
  $routes->add('login', 'Auth::login');
  $routes->add('logout', 'Auth::logout');
  $routes->add('forgot', 'Auth::forgot_password');
  // ...

  // ... user centric
  $routes->add('list', 'User::list');
  $routes->add('user', 'User::profile');
  $routes->add('create', 'User::create');
  $routes->add('edit', 'User::edit');
  $routes->add('edit/(:hash)', 'User::edit/$1');
  $routes->add('activate/(:hash)', 'User::activate/$1');
  $routes->add('suspend/(:hash)', 'User::suspend/$1');
  $routes->delete('delete/(:hash)', 'User::delete/$1');
  // ...

  // ... role/roles
  $routes->add('role/create', 'Group::create');
  $routes->patch('role/edit/(:hash)', 'Group::edit/$1');
  $routes->add('suspend/(:hash)', 'Group::suspend/$1');
  $routes->delete('delete/(:hash)', 'Group::delete/$1');
  // ...

  // ... be careful leaving these in if you aren't sure what they do
  $routes->add('admin', 'Admin::go_away');
  $routes->add('admin/generate', 'Admin::salt');
  $routes->add('admin/generate/(:num)', 'Admin::salt/$1');
  $routes->add('admin/install?(:hash)', 'Admin::install/$1');
  $routes->add('admin/uninstall?(:hash)', 'Admin::uninstall/$1');
  // ...

});
