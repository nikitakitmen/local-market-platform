<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    // $this->authorize('update', $product) — проверка прав через политики (app/Policies)
    use AuthorizesRequests;
}
