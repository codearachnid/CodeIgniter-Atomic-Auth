<?php namespace AtomicAuth\Entities;

use CodeIgniter\Entity;

class Role extends Entity
{

  protected $id;
  protected $guid;
  protected $name;
  protected $description;
  protected $status;
  // TODO do status value mapping between database and middletier
  public $statusValueMap = [
    'inactive' => 0,
    'active' => 1,
  ];

  public function setStatus(string $key)
  {
      $this->attributes['status'] = isset($statusValueMap[ $key ]) ? $statusValueMap[ $key ] : $key;
      return $this;
  }
  public function getStatus(string $key)
  {
    return isset($statusValueMap[ $key ]) ? $statusValueMap[ $key ] : $key;
  }

}
