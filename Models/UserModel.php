<?php namespace AtomicAuth\Models;

use CodeIgniter\Model;

class UserModel extends Model
{

  protected $table         = 'atomicauth_users'; // TODO make this dynamically driven via config
  protected $allowedFields = [
      'email', 'password_hash', 'status', 'status_message'
  ];
  protected $returnType    = 'AtomicAuth\Entities\User';
  protected $useTimestamps = true;

}
