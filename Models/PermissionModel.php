<?php namespace AtomicAuth\Models;

use CodeIgniter\Model;

class GroupModel extends Model
{
  protected $table         = 'atomicauth_groups'; // TODO make this dynamically driven via config
  protected $allowedFields = [
      'guid', 'name', 'description'
  ];
  protected $returnType    = 'AtomicAuth\Entities\Group';
  protected $useTimestamps = true;

  public function getPermissionByName( string $name = null )
  {
    if( empty ( $name ) )
    {
      return null;
    }
    return $this->asObject()->where('name', $name)->limit(1)->first();
  }

}
