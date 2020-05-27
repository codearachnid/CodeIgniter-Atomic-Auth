<?php namespace AtomicAuth\Entities;

use CodeIgniter\Entity;

class Profile extends Entity
{
  protected $identity;
  protected $email;
  protected $id;
  protected $guid;
  protected $last_check;
  protected $capabilities;
  protected $roles;
}
