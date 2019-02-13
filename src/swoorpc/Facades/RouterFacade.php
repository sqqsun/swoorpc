<?php

namespace Swoorpc\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Illuminate\View\Factory
 */
class RouterFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'swoorpc.router.facade';
    }
}
