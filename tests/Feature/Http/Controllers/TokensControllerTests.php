<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Token;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Facades\File;
use Tests\DatabaseTestCase;

abstract class TokensControllerTests extends DatabaseTestCase
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
     * Verify that the function is a shortcut for identifying whether the main database is sqlite or not
     */
    public function testUsingsqlite(): void
    {
        $this->assertFalse(usingsqlite());
    }

    /**
     * Ensure the homepage redirects to the codes list
     * - no extra code coverage but nice to have
     */
    public function testNoRootPage(): void
    {
        $this->get('/')
            ->assertRedirect(route('tokens.code'));
    }

    /**
     * Ensure that logged out users are forced to the login page
     */
    public function testNoAuth(): void
    {
        $this->assertGuest();

        $this->get(route('tokens.code'))
            ->assertRedirect(route('login'));
    }

    /**
     * Make sure a logged in user is logged out if the encryption key is not set in their session
     */
    public function testMissingEncryptionkey(): void
    {
        $this->actingAsTestingUser()
            ->get(route('tokens.code'))
            ->assertOk();

        $this->assertTrue(session()->has('encryptionkey'));

        // creating the testing user sets an encryption key
        session()->forget('encryptionkey');

        $this->assertFalse(session()->has('encryptionkey'));

        $this->actingAsTestingUser()
            ->get(route('tokens.code'))
            ->assertRedirect(route('login'));
    }

    /**
     * Make sure a user can see a list of their tokens
     */
    public function testViewList(): void
    {
        $this->actingAsTestingUser()
            ->get(route('tokens.code'))
            ->assertOk()
            ->assertViewIs('tokens.list');
    }

    /**
     * Make sure a user can see the totp code for a token
     */
    public function testViewToken(): void
    {
        $token = Token::factory()
            ->for($this->getTestingUser())
            ->create();

        // I don't know how to explain the number of times this happens
        // - stan says that times does not exist but it does
        // @phpstan-ignore method.notFound
        File::partialMock()
            ->shouldReceive('exists')
            ->times(3)
            ->andReturn(
                false, // where is this?
                false, // first request
                true,  // second request
            );

        // thanks stan
        $this->assertNotNull($token->path);

        $this->actingAsTestingUser()
            ->get(route('tokens.code', $token->path))
            ->assertOk()
            ->assertViewIs('tokens.code')
            ->assertViewHas('image', fn ($value) => $value === false);

        $this->actingAsTestingUser()
            ->get(route('tokens.code', $token->path))
            ->assertOk()
            ->assertViewIs('tokens.code')
            ->assertViewHas(
                'image',
                fn ($value) => $value === 'tokenicons/' . trim($token->path, '/') . '.png'
            );

        $this->actingAsTestingUser()
            ->getJson(route('tokens.code', $token->path))
            ->assertOk()
            ->assertJsonStructure([
                'code',
                'refreshat'
            ]);
    }

    /**
     * Make sure a user gets a 404 page if a token cannot be found
     */
    public function testViewNotAToken(): void
    {
        $this->actingAsTestingUser()
            ->get(route('tokens.code', 'notatoken'))
            ->assertNotFound();
    }

    /**
     * Make sure a user can see export a token
     */
    public function testExportToken(): void
    {
        $token = Token::factory()
            ->for($this->getTestingUser())
            ->create();

        config([
            'app.allowexport' => false,
        ]);

        $this->actingAsTestingUser()
            ->get(route('tokens.export', $token->path))
            ->assertNotFound();

        config([
            'app.allowexport' => true,
        ]);

        $this->actingAsTestingUser()
            ->get(route('tokens.export', $token->path))
            ->assertOk()
            ->assertViewIs('tokens.export');
    }

    public function testTokenFolders(): void
    {
        config([
            'app.allowexport' => true,
        ]);

        // set a specific path to avoid random url encoding problem
        $path = Token::formatPath('testexporttokenredirect');

        Token::factory()
            ->for($this->getTestingUser())
            ->state(new Sequence(
                [
                    'path' => "{$path}/Alpha", // bad path fixed by token model saving
                ],
                [
                    'path' => "{$path}/Beta", // bad path fixed by token model saving
                ]
            ))
            ->count(2)
            ->create();

        /**
         * Make sure a user is redirected to the codes list
         * if they try and export a folder
         */
        $this->actingAsTestingUser()
            ->get(route('tokens.export', $path))
            ->assertRedirect(route('tokens.code', $path));

        Token::factory()
            ->create([
                'path' => 'somewhereelse/Charlie',
            ]);

        $this->actingAsTestingUser()
            ->get(route('tokens.export', '/somewhereelse/'))
            ->assertNotFound();

        $this->actingAsTestingUser()
            ->get(route('tokens.code', $path))
            ->assertOk();
    }

    /**
     * Ensure a user has a chance to create a new token
     */
    public function testCreateToken(): void
    {
        $this->actingAsTestingUser()
            ->get(route('tokens.create'))
            ->when(
                $this->readonly,
                fn ($r) => $r
                    ->assertNotFound(),
                fn ($r) => $r
                    ->assertOk()
                    ->assertViewIs('tokens.form'),
            );
    }

    public function testStoreToken(): void
    {
        if ($this->readonly) {
            $this->actingAsTestingUser()
                ->post(route('tokens.store'))
                ->assertNotFound();

            return;
        }

        $newFakeToken = Token::factory()
            ->for($this->getTestingUser()) // make sure encryption secret matches
            ->make();

        $this->actingAsTestingUser()
            ->post(route('tokens.store'), [
                'path' => '',
                'title' => $newFakeToken->title,
                'secret' => $newFakeToken->getDecryptedSecret(),
            ])
            ->assertSessionHas('message', 'Check your input and try again')
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('tokens.create'));

        $this->actingAsTestingUser()
            ->post(route('tokens.store'), [
                'path' => $newFakeToken->path,
                'title' => '',
                'secret' => $newFakeToken->getDecryptedSecret(),
            ])
            ->assertSessionHas('message', 'Check your input and try again')
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('tokens.create'));

        $this->actingAsTestingUser()
            ->post(route('tokens.store'), [
                'path' => $newFakeToken->path,
                'title' => $newFakeToken->title,
                'secret' => '',
            ])
            ->assertSessionHas('message', 'Check your input and try again')
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('tokens.create'));

        $this->assertDatabaseCount('tokens', 0);

        $response = $this->actingAsTestingUser()
            ->post(route('tokens.store'), [
                'path' => $newFakeToken->path,
                'title' => $newFakeToken->title,
                'secret' => $newFakeToken->getDecryptedSecret(),
            ])
            ->assertSessionMissing('message')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('tokens', 1);

        $token = Token::first();

        $this->assertNotNull($token);

        $response->assertRedirect(route('tokens.code', $token->path));
    }

    /**
     * Ensure a user can see the details of a token
     */
    public function testTokenShow(): void
    {
        $token = Token::factory()
            ->for($this->getTestingUser())
            ->create();

        $this->actingAsTestingUser()
            ->get(route('tokens.show', $token->id_hash))
            ->assertOk()
            ->assertViewIs('tokens.show');
    }

    /**
     * Ensure a user has a chance to edit the token details
     */
    public function testTokenEdit(): void
    {
        $token = Token::factory()
            ->for($this->getTestingUser())
            ->create();

        $this->actingAsTestingUser()
            ->get(route('tokens.edit', $token->id_hash))
            ->when(
                $this->readonly,
                fn ($r) => $r
                    ->assertNotFound(),
                fn ($r) => $r
                    ->assertOk()
                    ->assertViewIs('tokens.form'),
            );
    }

    public function testUpdateToken(): void
    {
        $token = Token::factory()
            ->for($this->getTestingUser())
            ->create();

        if ($this->readonly) {
            $this->actingAsTestingUser()
                ->post(route('tokens.update', $token->id_hash))
                ->assertNotFound();

            return;
        }

        $this->actingAsTestingUser()
            ->post(route('tokens.update', $token->id_hash), [
                'path' => '',
                'title' => '',
            ])
            ->assertRedirect()
            ->assertSessionHas('message')
            ->assertSessionHasErrors([
                'path',
                'title',
            ]);

        session()->forget('message');

        $this->assertNotSame('/test/', $token->path);
        $this->assertNotSame('test', $token->title);

        $this->actingAsTestingUser()
            ->post(route('tokens.update', $token->id_hash), [
                'path' => 'test',
                'title' => 'test',
            ])
            ->assertRedirect()
            ->assertSessionMissing('message')
            ->assertSessionHasNoErrors();

        $token->refresh();

        $this->assertSame('/test/', $token->path);
        $this->assertSame('test', $token->title);
    }

    public function testDeleteToken(): void
    {
        $token = Token::factory()
            ->for($this->getTestingUser())
            ->create();

        $this->actingAsTestingUser()
            ->get(route('tokens.delete', $token->id_hash))
            ->when(
                $this->readonly,
                fn ($r) => $r
                    ->assertNotFound(),
                fn ($r) => $r
                    ->assertOk()
                    ->assertViewIs('tokens.delete'),
            );
    }

    public function testDestroyToken(): void
    {
        $token = Token::factory()
            ->for($this->getTestingUser())
            ->create();

        if ($this->readonly) {
            $this->actingAsTestingUser()
                ->delete(route('tokens.destroy', $token->id_hash))
                ->assertNotFound();

            return;
        }

        $this->actingAsTestingUser()
            ->delete(route('tokens.destroy', $token->id_hash))
            ->assertRedirect(route('tokens.code'));

        $this->assertFalse(Token::where('id', $token->id)->exists());
    }
}
