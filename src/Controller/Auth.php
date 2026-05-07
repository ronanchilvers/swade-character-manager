<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity;
use App\Entity\Factory\User;
use App\Filter;
use Exception;
use Flight;
use Throwable;

class Auth
{
    private const LOGIN_FAILED_MESSAGE = 'Login failed';

    public function __construct(private User $factory)
    {
    }

    public function index(): void
    {
        $url = Flight::google()->getAuthorizationUrl();
        Flight::session()->oauth2state = Flight::google()->getState();

        Flight::render('auth/index.twig', [
            'url' => $url,
        ]);
    }

    public function logout(): void
    {
        Flight::session()->delete('user');
        Flight::redirect('/');
        exit;
    }

    public function return(): void
    {
        $request = Flight::request();
        $session = Flight::session();
        foreach (['state', 'code', 'error'] as $key) {
            if (!isset($request->query[$key])) {
                $$key = null;
                continue;
            }
            $$key = Filter::noTags($request->query[$key]);
        }

        // Check for errors
        /* @var $error string */
        if (!empty($error)) {
            $this->failLogin($session);
            return;
        }

        // Check the state is valid
        /* @var $state string */
        if (empty($state) || $session->oauth2state !== $state) {
            $this->failLogin($session);
            return;
        }
        $session->delete('oauth2state');

        // Retrieve user data
        try {
            $google = Flight::google();
            /* @var $code string */
            $token = $google->getAccessToken('authorization_code', [
                'code' => $code,
            ]);
            $googleUser = $google->getResourceOwner($token);

            $user = $this->factory->byEmail((string) $googleUser->getEmail());
            if (is_null($user)) {
                $user = User::createFromGoogleUser($googleUser);
                $result = $this->factory->insert($user);
                if (!$result->isSuccess()) {
                    throw new Exception('Unable to create new user account');
                }
            }
        } catch (Throwable $ex) {
            $this->failLogin($session);
            return;
        }

        if (!$this->factory->isActive($user)) {
            $this->failLogin($session);
            return;
        }

        $session->user = $user;

        Flight::redirect('/');
        return;
    }

    private function failLogin(object $session): void
    {
        if (method_exists($session, 'delete')) {
            $session->delete('user');
            $session->delete('oauth2state');
        } else {
            unset($session->user, $session->oauth2state);
        }

        if (method_exists($session, 'error')) {
            $session->error(static::LOGIN_FAILED_MESSAGE);
        }

        Flight::redirect('/auth');
    }
}
