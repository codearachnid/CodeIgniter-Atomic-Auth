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

  public function getGroupByGuid( string $guid = null )
  {
    if( empty ( $guid ) )
    {
      return null;
    }
    return $this->asObject()->where('guid', $guid)->limit(1)->first();
  }

}
