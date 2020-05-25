# CodeIgniter Atomic Auth
A performant and rich user authentication library. Fully extensible and portable, secure auth package for CodeIgniter 4.

[![](https://github.com/codearachnid/CodeIgniter-Atomic-Auth/workflows/PHP%20Tests/badge.svg)](https://github.com/codearachnid/CodeIgniter-Atomic-Auth/actions?query=workflow%3A%22PHP+Tests%22)

**NOTE: This package is under early development and is not ready for prime-time.**

This repo is maintained by volunteers. If you post an issue and haven't heard from us within 7 days, feel free to ping the issue so that we see it again.


## Install

Before installing, please check that you are meeting the minimum server requirements. AtomicAuth needs CodeIgniter 4.x, PHP 7.1. If you want to use Composer or manually load files into your system structure.


- Order your files into a similar path structure of application (case is irrelevant as long as path is accurate)
```
# → Root Directory
└── application/
└── AtomicAuth/
└── system/
```

- Then in your App/Config/Autoload.php, add this into the `$psr4` array:

```
'AtomicAuth'  => ROOTPATH . 'AtomicAuth',
```

- Implement this in the BaseController `init` constructor.
```
//Pre loads the Authentication Library
$this->atomicAuth = new \AtomicAuth\Libraries\AtomicAuth();
helper('AtomicAuth\auth');
```

## Configuration

// TODO write configuration and helpers

## Requirements

- PHP 7.2+
- CodeIgniter 4. Changes in beta-3 require the latest develop branch of CodeIgniter 4 to work correctly (4.0.3 won't do).

## Credit (on the shoulders of giants I stand)
* Ben Edmunds - IonAuth https://github.com/benedmunds/CodeIgniter-Ion-Auth
* Lonnie Ezell - MythAuth https://github.com/lonnieezell/myth-auth/
* WordPress for user meta structure
