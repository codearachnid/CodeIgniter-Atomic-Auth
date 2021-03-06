<?php namespace AtomicAuth\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'atomicauth_users'; // TODO make this dynamically driven via config
    protected $allowedFields = [
      'guid', 'email', 'password_hash', 'status', 'status_message', 'reset_hash', 'reset_at','reset_expires'
  ];
    protected $returnType    = 'AtomicAuth\Entities\User';
    protected $useTimestamps = true;


    /**
     * Checks email to see if the email is already registered.
     *
     * @param string $email Email to check
     *
     * @return boolean true if the user is registered false if the user is not registered.
     */
    public function emailExists(string $email=null): bool
    {
        $this->triggerEvents('emailCheck');

        if (is_null($email)) {
            return false;
        }

        return $this->where('email', $email)
                            ->limit(1)
                            ->countAllResults() > 0;
    }

    /**
     * Identity check : Check to see if the identity is already registered
     *
     * @param string $identity Identity
     *
     * @return boolean
     */
    public function identityExists(string $identity=null): bool
    {
        if (is_null($identity)) {
            return false;
        }

        $config = config('AtomicAuth');
        return $this->where($config->identity, $identity)
                    ->limit(1)
                    ->countAllResults() > 0;
    }

    /**
     * Get user ID from identity
     *
     * @param string $identity Identity
     *
     * @return boolean|integer
     */
    public function getByIdentity(string $identity='', string $status = 'all')
    {
        if (empty($identity)) {
            return null;
        }

        $config = config('AtomicAuth');
        $builder = $this->asObject()->where($config->identity, $identity);

        if( $status != 'all' )
        {
          $builder->where('status', $status);
        }

        return $builder->limit(1)->first();
    }

    public function getByGuid(string $guid = null)
    {
        if (empty($guid)) {
            return null;
        }

        return $this->asObject()->where('guid', $guid)->limit(1)->first();
    }
}
