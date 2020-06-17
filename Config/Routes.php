<?php namespace Config;

/**
 * Be careful leaving these in if you aren't sure what they do.
 */
$routes->group('auth', ['namespace' => 'AtomicAuth\Controllers'], function ($routes) {

    // ... generic auth routes
    $routes->add('/', 'Auth::index');
    $routes->add('login', 'Auth::login');
    $routes->add('logout', 'Auth::logout');
    $routes->add('forgot', 'Auth::forgot_password');
    $routes->add('reset', 'Auth::reset_password');
    // ...

    // ... user centric
    $routes->add('list', 'User::list');
    $routes->add('list/(:alphanum)', 'User::list/$1');
    $routes->add('user', 'User::profile');
    $routes->add('create', 'User::create');
    $routes->add('edit', 'User::edit');
    $routes->add('edit/(:hash)', 'User::edit/$1');
    $routes->add('activate/(:hash)', 'User::activate/$1');
    $routes->add('suspend/(:hash)', 'User::suspend/$1');
    $routes->delete('delete/(:hash)', 'User::delete/$1');
    // ...

    // ... role/roles
    $routes->add('role/list', 'Role::list');
    $routes->add('role/list/(:hash)', 'Role::list');
    $routes->add('role/create', 'Role::create');
    $routes->add('role/edit/(:hash)', 'Role::edit/$1');
    $routes->delete('role/delete/(:hash)', 'Role::delete/$1');
    // ...

    // ... capability/capabilities
    $routes->add('capability/list', 'Capability::list');
    $routes->add('capability/list/(:hash)', 'Capability::list');
    $routes->add('capability/create', 'Capability::create');
    $routes->add('capability/edit/(:hash)', 'Capability::edit/$1');
    $routes->delete('capability/delete/(:hash)', 'Capability::delete/$1');
    // ...

    // ... admin management for Atomic Auth
    $routes->add('admin', 'Admin::go_away');
    $routes->add('admin/generate', 'Admin::salt');
    $routes->add('admin/generate/(:num)', 'Admin::salt/$1');
    $routes->add('admin/install?(:hash)', 'Admin::install/$1');
    $routes->add('admin/uninstall?(:hash)', 'Admin::uninstall/$1');
    // ...

    // ...
    $routes->get('api/user/(:hash)', 'Api:get_user/$1');
    // ...
});
