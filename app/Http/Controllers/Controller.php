<?php

namespace App\Http\Controllers;

use App\Traits\Auditable;
use Laramin\Utility\Onumoti;

abstract class Controller
{
    use Auditable;

    public function __construct()
    {
        $className = get_called_class();
        Onumoti::mySite($this,$className);
    }

    public static function middleware()
    {
        return [];
    }

}
