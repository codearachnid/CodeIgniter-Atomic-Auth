<?php namespace AtomicAuth\Entities;

use CodeIgniter\Entity;

class Message extends Entity
{
    protected $guid;
    protected $level;
    protected $type;
    protected $message;
}
