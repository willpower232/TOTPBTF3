<?php

namespace Tests\Feature\Http\Controllers;

class TokensControllerReadOnlyTest extends TokensControllerTests
{
    protected bool $readonly = true;
}
