<?php
namespace Devbot\Plugin\Drupal\Db;

/**
 * Settings to connect to a MySQL instance
 */
class MysqlSettings
{
    protected $host;
    protected $username;
    protected $password;
    protected $port;
    protected $database;
    protected $prefix;
    
    public function getHost()
    {
        return $this->host;
    }
    
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }
    
    public function getUsername()
    {
        return $this->username;
    }
    
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }
    
    public function getPassword()
    {
        return $this->password;
    }
    
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }
    
    public function getPort()
    {
        return $this->port;
    }
    
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }
    
    public function getDatabase()
    {
        return $this->database;
    }
    
    public function setDatabase($database)
    {
        $this->database = $database;
        return $this;
    }
    
    public function getPrefix()
    {
        return $this->prefix;
    }
    
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }
}