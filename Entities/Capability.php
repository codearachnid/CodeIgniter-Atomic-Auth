<?php namespace AtomicAuth\Entities;

use CodeIgniter\Entity;

class Capability extends Entity
{
    protected $id;
    protected $name;
    protected $description;
    protected $status;
    // TODO do status value mapping between database and middletier
    public $statusValueMap = [
    'inactive' => 0,
    'active' => 1,
  ];
}
