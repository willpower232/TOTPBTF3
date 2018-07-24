<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class SessionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function setLightMode()
    {
        $user = auth()->user();

        $user->light_mode = filter_var(request('light_mode'), FILTER_VALIDATE_BOOLEAN);
        $user->save();

        return response()->json(array(
            'current_state' => $user->light_mode,
        ), 200);
    }
}
