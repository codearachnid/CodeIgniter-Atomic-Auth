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

  public function getPermissionByKey( string $key = null )
  {
    if( empty ( $key ) )
    {
      return null;
    }
    return $this->asObject()->where('guid', $key)->limit(1)->first();
  }

}
