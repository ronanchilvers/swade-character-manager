<?php

declare(strict_types=1);

use App\Controller\Admin\Characters as AdminCharacters;
use App\Controller\Admin\Campaigns as AdminCampaigns;
use App\Controller\Admin\Users as AdminUsers;
use App\Controller\Auth;
use App\Controller\Campaigns;
use App\Controller\Character\Base;
use App\Controller\Character\Hindrances;
use App\Controller\Character\Attributes;
use App\Controller\Character\Edges;
use App\Controller\Character\Settings;
use App\Controller\Character\Sheet;
use App\Controller\Character\Skills;
use App\Controller\Home;
use App\Middleware\Auth as MiddlewareAuth;
use App\Middleware\Superuser as MiddlewareSuperuser;

// Variables available for registering services:
// - $container - A flightphp/Container instance
// - $settings - The application configuration array

/* @var $settings array */
/* @var $container \flight\Container */

Flight::route('GET /', [Home::class, 'index'])
    ->setAlias('home_page')
    ->addMiddleware(MiddlewareAuth::class);

Flight::route('GET /characters/shared/@token:[a-f0-9]{64}', [Sheet::class, 'shared'])
    ->setAlias('characters_public_sheet');

// Authentication
Flight::group('/auth', function () {
    Flight::route('GET /', [Auth::class, 'index'])->setAlias('auth_login');
    Flight::route('GET /return', [Auth::class, 'return']);
    Flight::route('GET /logout', [Auth::class, 'logout'])->setAlias('auth_logout');
});

// Admin
Flight::group('/admin', function () {
    Flight::group('/campaigns', function () {
        Flight::route('GET /', [AdminCampaigns::class, 'index'])
            ->setAlias('admin_campaigns_index');
        Flight::route('GET /@hash:[a-z0-9]{32}', [AdminCampaigns::class, 'view'])
            ->setAlias('admin_campaigns_view');
    });
    Flight::group('/characters', function () {
        Flight::route('GET /@id:[0-9]+', [AdminCharacters::class, 'index'])
            ->setAlias('admin_characters_index');
    });
    Flight::group('/users', function () {
        Flight::route('GET /', [AdminUsers::class, 'index'])
            ->setAlias('admin_users_index');
        Flight::route('GET|POST /@id:[0-9]+', [AdminUsers::class, 'edit'])
            ->setAlias('admin_users_edit');
        Flight::route('POST /@id:[0-9]+/disable', [AdminUsers::class, 'disable'])
            ->setAlias('admin_users_disable');
        Flight::route('POST /@id:[0-9]+/enable', [AdminUsers::class, 'enable'])
            ->setAlias('admin_users_enable');
    });
}, [MiddlewareAuth::class, MiddlewareSuperuser::class]);

// Campaigns
Flight::group('/campaigns', function () {
    Flight::route('GET /', [Campaigns::class, 'index'])
        ->setAlias('campaigns_index');
    Flight::route('GET|POST /create', [Campaigns::class, 'create'])
        ->setAlias('campaigns_create');
    Flight::route('GET /@hash:[a-z0-9]{32}', [Campaigns::class, 'view'])
        ->setAlias('campaigns_view');
    Flight::route('GET|POST /@hash:[a-z0-9]{32}/edit', [Campaigns::class, 'edit'])
        ->setAlias('campaigns_edit');
    Flight::route('GET|POST /join/@hash:[a-z0-9]{32}', [Campaigns::class, 'join'])
        ->setAlias('campaigns_join');
    Flight::route('POST /@hash:[a-z0-9]{32}/characters', [Campaigns::class, 'addCharacter'])
        ->setAlias('campaigns_add_character');
    Flight::route('POST /@hash:[a-z0-9]{32}/characters/@character_hash:[a-z0-9]{32}/leave', [Campaigns::class, 'leaveCharacter'])
        ->setAlias('campaigns_leave_character');
    Flight::route('POST /@hash:[a-z0-9]{32}/leave', [Campaigns::class, 'leave'])
        ->setAlias('campaigns_leave');
    Flight::route('GET /@hash:[a-z0-9]{32}/reset', [Campaigns::class, 'reset'])
        ->setAlias('campaigns_reset');
}, [MiddlewareAuth::class]);

// Characters
Flight::group('/characters', function () {
    Flight::route('GET|POST /create', [Base::class, 'create'])
        ->setAlias('characters_create');
    Flight::route('POST /delete/@hash:[a-z0-9]{32}', [Base::class, 'delete'])
        ->setAlias('characters_delete');
    Flight::route('GET|POST /settings/@hash:[a-z0-9]{32}', [Settings::class, 'index'])
        ->setAlias('characters_settings');
    Flight::route('GET|POST /concept/@hash:[a-z0-9]{32}', [Base::class, 'index'])
        ->setAlias('characters_concept');
    Flight::route('GET|POST /hindrances/@hash:[a-z0-9]{32}', [Hindrances::class, 'index'])
        ->setAlias('characters_hindrances');
    Flight::route('GET|POST /attributes/@hash:[a-z0-9]{32}', [Attributes::class, 'index'])
        ->setAlias('characters_attributes');
    Flight::route('GET|POST /skills/@hash:[a-z0-9]{32}', [Skills::class, 'index'])
        ->setAlias('characters_skills');
    Flight::route('GET|POST /edges/@hash:[a-z0-9]{32}', [Edges::class, 'index'])
        ->setAlias('characters_edges');
    Flight::route('GET /sheet/@hash:[a-z0-9]{32}', [Sheet::class, 'index'])
        ->setAlias('characters_sheet');
    Flight::route('POST /sheet/@hash:[a-z0-9]{32}/state', [Sheet::class, 'updateState'])
        ->setAlias('characters_sheet_state');
    Flight::route('POST /sheet/@hash:[a-z0-9]{32}/notes', [Sheet::class, 'updateNotes'])
        ->setAlias('characters_sheet_notes');
    Flight::route('POST /sheet/@hash:[a-z0-9]{32}/gear', [Sheet::class, 'updateGear'])
        ->setAlias('characters_sheet_gear');
    Flight::route('POST /sheet/@hash:[a-z0-9]{32}/weapons', [Sheet::class, 'updateWeapons'])
        ->setAlias('characters_sheet_weapons');
}, [ MiddlewareAuth::class ]);
