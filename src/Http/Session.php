<?php

namespace App\Http;

use App\Http\Session\StorageInterface;
use flight\net\Request;

/**
 * Session helper class
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Session
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var \App\Http\Session\StorageInterface
     */
    protected $storage;

    /**
     * Class constructor
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Initialise the session
     *
     * @param \flight\net\Request $request
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function initialise(Request $request): void
    {
        $this->data = $this->storage->initialise($request);
    }

    /**
     * Shutdown the session
     *
     * @return void
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function shutdown(): void
    {
        $this->storage->shutdown($this->data);
    }

    /**
     * Set a session variable
     *
     * @param string $key
     * @param mixed $value
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Magic method to set a session variable
     *
     * @param string $key
     * @param mixed $value
     * @author Ronan Chilvers <ronan@thelittledot.com>
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Get a session value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function get($key, $default = null)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return $default;
    }

    /**
     * Magic method to get a session variable
     *
     * @param string $key
     * @return mixed
     * @author Ronan Chilvers <ronan@thelittledot.com>
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Delete a session key
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function delete($key)
    {
        if ($this->has($key)) {
            unset($this->data[$key]);
        }
    }

    /**
     * Does the session have a key?
     *
     * @param string $key
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function has($key)
    {
        return isset($this->data[$key]);
    }

    public function __isset($key)
    {
        return $this->has($key);
    }

    public function __unset($key)
    {
        $this->delete($key);
    }

    /**
     * Set a flash message
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function flash($message, $type = 'info')
    {
        if (!isset($this->data['flash'])) {
            $this->data['flash'] = [];
        }
        if (!isset($this->data['flash'][$type])) {
            $this->data['flash'][$type] = [];
        }
        $this->data['flash'][$type][] = $message;
    }

    /**
     * Get a set of flash messages for a given type
     *
     * @param string $type
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getFlash($type)
    {
        if (!isset($this->data['flash'], $this->data['flash'][$type])) {
            return null;
        }
        if (empty($this->data['flash'][$type])) {
            return null;
        }
        $messages = $this->data['flash'][$type];
        unset($this->data['flash'][$type]);

        return $messages;
    }

    /**
     * Get all flash messages
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getFlashes()
    {
        if (!isset($this->data['flash'])) {
            return null;
        }
        $flashes = $this->data['flash'];
        unset($this->data['flash']);

        return $flashes;
    }
}
