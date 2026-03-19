<?php

namespace App\Http\Session;

use App\Http\Cookie;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;
use flight\net\Request;

/**
 * Cookie storage handler
 *
 * This handler uses a cookie to store session data
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class CookieStorage implements StorageInterface
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * @var \Defuse\Crypto\Key|null
     */
    protected $key = null;

    /**
     * Class constructor
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct($settings = [])
    {
        $defaults = [
            'name'           => 'app_session',
            'expires'        => 0,
            'path'           => '/',
            'domain'         => null,
            'secure'         => true,
            'httponly'       => true,
            'samesite'       => Cookie::SAMESITE_LAX,
            'encryption.key' => null
        ];
        $this->settings = array_merge(
            $defaults,
            $settings
        );
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function initialise(Request $request): array
    {
        $cookie = Cookie::get(
            $request,
            $this->settings['name']
        );
        $data = $cookie->getData();
        if (is_null($data)) {
            return [];
        }
        try {
            $data = Crypto::decrypt(
                $data,
                $this->getKey()
            );
            $data = @unserialize($data);
        } catch (WrongKeyOrModifiedCiphertextException $ex) {
            $data = null;
        }
        if (!is_array($data)) {
            $data = [];
        }

        return $data;
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function shutdown(array $data): void
    {
        $data = serialize($data);
        $data = Crypto::encrypt(
            $data,
            $this->getKey()
        );
        $cookie = Cookie::create(
            $this->settings['name'],
            $data
        );
        $cookie = $cookie->expires($this->settings['expires'])
                         ->path($this->settings['path'])
                         ->domain($this->settings['domain'])
                         ->secure($this->settings['secure'])
                         ->httpOnly($this->settings['httponly'])
                         ->sameSite($this->settings['samesite'])
                         ;
        if (!$cookie->set()) {
            return;
        }
    }

    /**
     * Get an encryption key object instance
     *
     * @return \Defuse\Crypto\Key
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function getKey()
    {
        if (!$this->key instanceof Key) {
            $this->key = Key::loadFromAsciiSafeString($this->settings['encryption.key']);
        }
        return $this->key;
    }
}
