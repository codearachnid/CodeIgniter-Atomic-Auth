<?php namespace AtomicAuth\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
  protected $table         = 'atomicauth_roles'; // TODO make this dynamically driven via config
  protected $allowedFields = [
      'guid', 'name', 'description', 'status'
  ];
  protected $returnType    = 'AtomicAuth\Entities\Role';
  protected $useTimestamps = true;

  public function setDbTable( string $name = null )
  {
    // TODO be a good keeper and check if table exists first?
    $this->table = !is_null($name) ? $name : $this->table;
  }

  public function getGroupByGuid( string $guid = null )
  {
    if( empty ( $guid ) )
    {
      return null;
    }
    return $this->asObject()->where('guid', $guid)->limit(1)->first();
  }


}
