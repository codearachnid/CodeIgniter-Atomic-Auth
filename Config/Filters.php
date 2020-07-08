<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
	// Makes reading things below nicer,
	// and simpler to change out script that's used.
	public $aliases = [
    'AtomicAuthLoginFilter'     => \AtomicAuth\Filters\LoginFilter::class,
  ];

	// Always applied before every request
	public $globals = [
    'before' => [
        //'honeypot'
        // 'csrf',
        'AtomicAuthLoginFilter',
    ]
  ];

	// Works on all of a particular HTTP method
	// (GET, POST, etc) as BEFORE filters only
	//     like: 'post' => ['CSRF', 'throttle'],
	// public $methods = [];

	// List filter aliases and any before/after uri patterns
	public $filters = [
    'isLoggedIn' => ['before' => ['role/*', 'capability/*', 'admin/*']],
  ];
}
