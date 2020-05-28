<?php namespace AtomicAuth\Models;

use CodeIgniter\Model;

class LoginModel extends Model
{
  protected $table         = 'atomicauth_track_logins AS trk'; // TODO make this dynamically driven via config
  protected $allowedFields = [
      'ip_address', 'identity', 'user_id', 'activity'
  ];
  protected $returnType    = 'AtomicAuth\Entities\Login';
  protected $useTimestamps = false;
  protected $identity = null;
  protected $lockoutTime = null;
  protected $limit = 1;

  public function getLoginsByIdentity( bool $excludeSuccess = false )
  {
    if( empty ( $this->identity ) )
    {
      return 0;
    }
    $builder = $this->asObject()->where('identity', $this->identity);
    if( !is_null($this->lockoutTime) ){
      $builder->where('created_at > ', date( "Y-m-d H:i:s", time() - $this->lockoutTime));
    }
    if( $excludeSuccess )
    {
      $builder->where('activity !=', 'success');
    }
    if( !is_null($this->limit) )
    {
      $builder->limit( $this->limit );
    }
    // consider opportunity for ip blocking
    $builder->orderBy('created_at', 'DESC');
    return $builder->findAll();
  }

  public function purgeByIdentity( string $identity = null )
  {
    if( !is_null( $identity ) )
    {
      $this->identity = $identity;
    }
    if( empty ( $this->identity ) )
    {
      return 0;
    }
    $builder = $this->asObject()->where('identity', $this->identity);
    if( !is_null($this->lockoutTime) ){
      $builder->where('created_at > ', date( "Y-m-d H:i:s", time() - $this->lockoutTime));
    }
    $builder->delete();
    // TODO build in records deleted return
  }

  public function __get($key)
    {
        if (property_exists($this, $key))
        {
            return $this->$key;
        }
    }

    public function __set($key, $value)
    {
        if (property_exists($this, $key))
        {
            $this->$key = $value;
        }
    }

}
