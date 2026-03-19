<?php

namespace App\Http\Session;

use flight\net\Request;

/**
 * Interface for session storage handlers
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
interface StorageInterface
{
    /**
     * Initialise the storage handler
     *
     * This method returns the current session state or an empty array
     *
     * @return void
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function initialise(Request $request): array;

    /**
     * Shut down the storage handler
     *
     * @param array $data
     * @return void
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function shutdown(array $data): void;
}
