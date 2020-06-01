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

    public function getUserByGuid(string $guid = null)
    {
        if (empty($guid)) {
            return null;
        }
        return $this->asObject()->where('guid', $guid)->limit(1)->first();
    }

    /**
       * Generate a GUID
       * @author Timothy Wood <codearachnid@gmail.com>
       */
    public function generateGuid()
    {
        $guid = bin2hex(random_bytes(16));

        // check if $guid exists in users table
        for ($i = 1; ; $i++) {
            if (! $this->where('guid', $guid)->first() || $i > 10) {
                break;
            }
            $guid = bin2hex(random_bytes(16));
        }

        return $guid;
    }
}
