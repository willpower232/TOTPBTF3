<?php

namespace Tests\Feature\Http\Controllers;

class SessionsControllerReadOnlyTest extends SessionsControllerTests
{
    protected bool $readonly = true;
}
