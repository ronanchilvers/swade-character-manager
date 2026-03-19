<?php

namespace App\Http\Session;

use flight\net\Request;

/**
 * Native storage handler
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class NativeStorage implements StorageInterface
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * Class constructor
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct($settings = [])
    {
        $current  = session_get_cookie_params();
        $defaults = [
            'name'         => 'app_session',
            'cache_limiter' => ini_get('session.cache_limiter'),
            'ini_settings' => []
        ];
        $this->settings = array_merge(
            $current,
            $defaults,
            $settings
        );
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function initialise(Request $request): array
    {
        session_set_cookie_params(
            $this->settings['lifetime'],
            $this->settings['path'],
            $this->settings['domain'],
            $this->settings['secure'],
            $this->settings['httponly']
        );
        session_name($this->settings['name']);
        session_cache_limiter($this->settings['cache_limiter']);
        session_start();

        $data = (isset($_SESSION)) ? $_SESSION : [];

        return $data;
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function shutdown(array $data): void
    {
        $_SESSION = $data;
    }
}
