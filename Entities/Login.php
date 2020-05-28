<?php namespace AtomicAuth\Entities;

use CodeIgniter\Entity;

class User extends Entity
{
    protected $identity;
    protected $ip_address;
    protected $user_id;
    protected $activity;
}
