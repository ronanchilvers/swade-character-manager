<?php

declare(strict_types=1);

namespace App\Http;

use flight\core\EventDispatcher;
use flight\net\Response as NetResponse;

class Response extends NetResponse
{
    public function sendHeaders(): self
    {
        EventDispatcher::getInstance()->trigger('flight.response.headers.before');

        return parent::sendHeaders();
    }
}
