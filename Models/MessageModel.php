<?php namespace AtomicAuth\Models;

/*
|--------------------------------------------------------------------------
| Message Type
|--------------------------------------------------------------------------
|
| The message will leverage the same log levels as CI4 core.
| https://codeigniter4.github.io/userguide/general/logging.html?highlight=logging
|
|	1 | emergency   => System is unusable
|	2 | alert       => Action Must Be Taken Immediately
| 3 | critical    => Application component unavailable, unexpected exception.
| 4 | error       => Don't need immediate action, but should be monitored.
| 5 | warning	    => Exceptional occurrences that are not errors.
| 6 | notice      => Normal but significant events.
| 7 | info        => Interesting events, like user logging in, etc.
| 8 | debug	      => Detailed debug information.
|
| If $this->config->logMessages is TRUE then all messages will be sent to CI logs
| when the message is set. There may be a delay if the user is shown the message
| to when it is logged by the system.
*/
class MessageModel
{
    /**
     * AtomicAuth config
     *
     * @var Config\AtomicAuth
     */
    protected $config;

    /**
     * CodeIgniter session
     *
     * @var \CodeIgniter\Session\Session
     */
    protected $session;

    protected $message;

    public function __construct()
    {
        $this->config = config('AtomicAuth');
        $this->session = session();
        $this->message = new \AtomicAuth\Entities\Message();
    }

    /**
     * Set a message
     *
     * @param string $message The message
     * @return string The given message
     */
    public function set(string $message, string $type = 'info'): string
    {
        if ($this->config->logMessages) {
            log_message($type, "{env}###{file}::{line} " . $message);
        }

        $messages = $this->session->getflashdata($this->config->sessionKeyMessages);
        $messages[] = ['message' => $message, 'type' => $type];
        $this->session->setflashdata($this->config->sessionKeyMessages, $messages);

        return $message;
    }

    /**
     * Get the messages
     * pass an array with threshold levels to show individual error types
     *
     * @return array
     */
    public function get(?array $filter = null): array
    {
        $messages = (array) $this->session->getflashdata($this->config->sessionKeyMessages);
        if(is_null($messages))
        {
          return [];
        }
        if (!is_null($filter)) {
            // TODO * ['emergency','alert','critical'] = Emergency, Alert, Critical messages
        }
        return $messages;
    }

    /**
     * Clear messages
     *
     * @return true
     */
    public function clear() : bool
    {
        return $this->session->setflashdata($this->config->sessionKeyMessages, null);
    }

    public function flash(string $message, string $type = 'info'): string
    {
      return $this->set($message, $type, flash);
    }
}
