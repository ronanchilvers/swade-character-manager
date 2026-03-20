<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity;
use App\Entity\Factory\User;
use App\Filter;
use Exception;
use Flight;

class Auth
{
    public function __construct(private User $factory)
    {
    }

    public function index()
    {
        $url = Flight::google()->getAuthorizationUrl();
        Flight::session()->oauth2state = Flight::google()->getState();

        Flight::render('auth/index.twig', [
            'url' => $url,
        ]);
    }

    public function logout()
    {
        Flight::session()->delete('user');
        Flight::redirect('/');
        exit;
    }

    public function return()
    {
        // http://localhost:8080/auth/return?state=c25da8f37a6834594269f8ededb5369d&iss=https%3A%2F%2Faccounts.google.com&code=4%2F0AfrIepA--mwt1MQ9-VyRptGOZBG45o1gC2wNCaTpifmpdKicuir7ZKrcG33YXdEt8kbMFA&scope=email+profile+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.profile+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.email+openid&authuser=0&hd=thelittledot.com&prompt=consent
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
            var_dump(__METHOD__, 'error(1): ' . $error);
            exit;
        }

        // Check the state is valid
        /* @var $state string */
        if (empty($state) || $session->oauth2state !== $state) {
            var_dump(__METHOD__, 'error(2): state mismatch');
            exit;
        }
        unset($session->oauth2state);

        // Retrieve user data
        try {
            $google = Flight::google();
            /* @var $code string */
            $token = $google->getAccessToken('authorization_code', [
                'code' => $code,
            ]);
            $googleUser = $google->getResourceOwner($token);

            $user = $this->factory->one(
                "user_email = ?",
                [$googleUser->getEmail()]
            );
            if (is_null($user)) {
                $user = User::createFromGoogleUser($googleUser);
                if (!$this->factory->insert($user)) {
                    throw new Exception('Unable to create new user account');
                }
            }
            $session->user = $user;

            Flight::redirect('/');
            return;
        } catch (Exception $ex) {
            var_dump(__METHOD__, 'error(3): ' . $ex->getMessage());
            exit;
        }
    }
}
