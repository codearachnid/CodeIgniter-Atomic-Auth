<?php namespace AtomicAuth\Entities;

use CodeIgniter\Entity;

class User extends Entity
{
    protected $id;
    protected $guid;
    protected $email;
    protected $password_hash;
    protected $status;
    protected $status_message;
    protected $reset_expires;
    protected $reset_hash;
    protected $reset_at;
}
