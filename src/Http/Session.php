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
    public function set(string $key, mixed $value): void
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
    public function __set(string $key, mixed $value): void
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
    public function get(string $key, mixed $default = null): mixed
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
    public function __get(string $key): mixed
    {
        return $this->get($key);
    }

    /**
     * Delete a session key
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function delete(string $key): void
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
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function __isset(string $key): bool
    {
        return $this->has($key);
    }

    public function __unset(string $key): void
    {
        $this->delete($key);
    }

    /**
     * Set a flash message
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function flash(string $message, string $type = 'info'): void
    {
        if (!isset($this->data['flash'])) {
            $this->data['flash'] = [];
        }
        $this->data['flash'][] = [ 'type' => $type, 'message' => $message ];
    }

    public function success(string $message): void
    {
        $this->flash($message, 'success');
    }

    public function info(string $message): void
    {
        $this->flash($message, 'info');
    }

    public function warning(string $message): void
    {
        $this->flash($message, 'warning');
    }

    public function error(string $message): void
    {
        $this->flash($message, 'error');
    }

    /**
     * Get all flash messages
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getFlashes(): ?array
    {
        if (!isset($this->data['flash'])) {
            return null;
        }
        $flashes = $this->data['flash'];
        unset($this->data['flash']);

        return $flashes;
    }
}
