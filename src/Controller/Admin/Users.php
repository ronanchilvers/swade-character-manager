<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity;
use App\Entity\Factory\User;
use App\Filter;
use Flight;

class Users
{
    public function __construct(private User $factory)
    {
    }

    public function index(): void
    {
        Flight::render('admin/users/index.twig', [
            'page_title' => 'Manage Users',
            'users' => $this->factory->ordered(),
        ]);
    }

    public function edit(string $id): void
    {
        $user = $this->findUser($id);
        if (!$user instanceof Entity) {
            Flight::session()->error('Unable to find user');
            Flight::redirect('/admin/users');
            return;
        }

        $errors = [];
        if ('POST' === Flight::request()->getMethod()) {
            $this->hydrateFromPost($user);
            $errors = $this->factory->validate($user);
            $selfErrors = $this->validateSelfUpdate($user);
            $errors = array_values(array_unique(array_merge($errors, $selfErrors)));

            if (!$errors) {
                $result = $this->factory->update($user);
                $errors = $result->errors();
                if (!$errors) {
                    Flight::session()->success(
                        sprintf('Saved user %s successfully', $user->email)
                    );
                    Flight::redirect($this->editUrl((int) $user->id));
                    return;
                }
            }

            if (!$selfErrors) {
                Flight::session()->error('Sorry! There was a problem!');
            }
        }

        Flight::render('admin/users/edit.twig', [
            'page_title' => 'Edit User',
            'entity' => $user,
            'errors' => $errors,
            'statuses' => User::statuses(),
            'is_self' => $this->isCurrentUser((int) $user->id),
        ]);
    }

    public function disable(string $id): void
    {
        $this->updateStatus($id, User::STATUS_INACTIVE);
    }

    public function enable(string $id): void
    {
        $this->updateStatus($id, User::STATUS_ACTIVE);
    }

    private function updateStatus(string $id, string $status): void
    {
        $user = $this->findUser($id);
        if (!$user instanceof Entity) {
            Flight::session()->error('Unable to find user');
            Flight::redirect('/admin/users');
            return;
        }

        if ($this->isCurrentUser((int) $user->id) && User::STATUS_INACTIVE === $status) {
            Flight::session()->error('You cannot disable your own account');
            Flight::redirect('/admin/users');
            return;
        }

        $user->status = $status;
        $result = $this->factory->update($user);
        if ($result->isSuccess()) {
            Flight::session()->success(
                sprintf(
                    '%s %s',
                    User::STATUS_ACTIVE === $status ? 'Re-enabled' : 'Disabled',
                    $user->email
                )
            );
        } else {
            Flight::session()->error('Sorry! There was a problem!');
        }

        Flight::redirect('/admin/users');
    }

    private function findUser(string $id): ?Entity
    {
        $userId = Filter::number($id);
        if ($userId <= 0) {
            return null;
        }

        return $this->factory->byId($userId);
    }

    private function hydrateFromPost(Entity $user): void
    {
        $user->firstname = trim(Filter::noTags($_POST['firstname'] ?? ''));
        $user->lastname = trim(Filter::noTags($_POST['lastname'] ?? ''));
        $user->superuser = isset($_POST['superuser']) ? 1 : 0;
        $user->status = trim(Filter::noTags($_POST['status'] ?? User::STATUS_ACTIVE));
    }

    private function validateSelfUpdate(Entity $user): array
    {
        if (!$this->isCurrentUser((int) $user->id)) {
            return [];
        }

        $errors = [];
        if (!(bool) $user->superuser) {
            $errors[] = 'superuser';
            Flight::session()->error('You cannot remove your own superuser access');
        }
        if (User::STATUS_INACTIVE === $user->status) {
            $errors[] = 'status';
            Flight::session()->error('You cannot disable your own account');
        }

        return array_values(array_unique($errors));
    }

    private function isCurrentUser(int $userId): bool
    {
        return isset(Flight::session()->user)
            && $userId === (int) Flight::session()->user->id;
    }

    private function editUrl(int $id): string
    {
        return sprintf('/admin/users/%d', $id);
    }
}
