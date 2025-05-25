<?php

namespace Tests\Feature\Http\Controllers;

use Tests\DatabaseTestCase;

abstract class SessionsControllerTests extends DatabaseTestCase
{
    protected bool $readonly = false;

    public function setUp(): void
    {
        parent::setUp();

        config([
            'app.readonly' => $this->readonly,
        ]);
    }

    /**
     * Make sure a user can see their details
     */
    public function testProfilePage(): void
    {
        $this->actingAsTestingUser()
            ->get(route('session.show'))
            ->assertOk()
            ->assertViewIs('sessions.show');
    }

    /**
     * Make sure a user has a chance to edit their details
     */
    public function testProfileEditPage(): void
    {
        $this->actingAsTestingUser()
            ->get(route('session.edit'))
            ->when(
                $this->readonly,
                fn ($r) => $r
                    ->assertNotFound(),
                fn ($r) => $r
                    ->assertOk()
                    ->assertViewIs('sessions.form'),
            );
    }

    /**
     * Ensure we can update user name and email address
     */
    public function testProfileUpdateDetails(): void
    {
        if ($this->readonly) {
            $this->actingAsTestingUser()
                ->post(route('session.update'))
                ->assertNotFound();

            return;
        }

        $oldname = $this->getTestingUser()->name;
        $newname = $oldname . ' III';

        $oldemail = $this->getTestingUser()->email;
        $newemail = $oldemail . '.uk';

        $this->actingAsTestingUser()
            ->post(route('session.update'), [
                'currentpassword' => 'password',
                'name' => $newname,
                'email' => $newemail,
            ])
            ->assertRedirect(route('session.show'));

        $this->refreshTestingUser();

        $this->assertSame($this->getTestingUser()->name, $newname);
        $this->assertSame($this->getTestingUser()->email, $newemail);
    }
}
