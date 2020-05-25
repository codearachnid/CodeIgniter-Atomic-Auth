<?php namespace AtomicAuth\Models;

use CodeIgniter\Model;

class LoginModel extends Model
{
  protected $table         = 'atomicauth_track_logins'; // TODO make this dynamically driven via config
  protected $allowedFields = [
      'ip_address', 'identity', 'user_id', 'activity'
  ];
  protected $returnType    = 'AtomicAuth\Entities\Login';
  protected $useTimestamps = false;
  protected $createdField  = 'created_at';
  public $lockoutTime = 0;

  public function getLoginsByIdentity( string $identity = null, int $limit = 20 )
  {
    if( empty ( $identity ) )
    {
      return null;
    }
    $builder = $this->asObject()->where('identity', $identity);
    $builder->where('created_at > ', date( "Y-m-d H:i:s", time() - $this->lockoutTime));
    if( !is_null($limit) )
    {
      $builder->limit( $limit );  
    }
    // consider opportunity for ip blocking
    $builder->orderBy('created_at', 'DESC');
    return $builder->findAll();
  }

}
