<?php namespace AtomicAuth\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'atomicauth_users'; // TODO make this dynamically driven via config
    protected $allowedFields = [
      'guid', 'email', 'password_hash', 'status', 'status_message'
  ];
    protected $returnType    = 'AtomicAuth\Entities\User';
    protected $useTimestamps = true;


    /**
     * Checks email to see if the email is already registered.
     *
     * @param string $email Email to check
     *
     * @return boolean true if the user is registered false if the user is not registered.
     * @author Mathew
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
     * @author Mathew
     */
    public function identityExists(string $identity=null): bool
    {
        $this->triggerEvents('identity_check');

        if (is_null($identity)) {
            return false;
        }

        $config = config('AtomicAuth');
        return $this->where($config->identity, $identity)
                           ->limit(1)
                           ->countAllResults() > 0;
    }

    public function getByGuid(string $guid = null)
    {
        if (empty($guid)) {
            return null;
        }

        return $this->asObject()->where('guid', $guid)->limit(1)->first();
    }
}
