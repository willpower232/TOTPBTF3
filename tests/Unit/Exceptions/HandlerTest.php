<?php
namespace Tests\Unit\Exceptions;

use Tests\TestCase;
use App\Exceptions\Handler;
use Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use \Illuminate\Contracts\Container\Container;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class HandlerTest extends TestCase
{
	/**
	 * Common exception handler testing routine
	 *
	 * @return void
	 */
	private function dothetest($code = 418, $expectedview = null)
	{
		$request = $this->createMock(Request::class);
		$exception = new HttpException($code);
		$handler = new Handler($this->createMock(Container::class));
		$response = $handler->render($request, $exception);

		if ($code !== 418) {
			$this->assertRegExp("/$expectedview\$/", $response->original->getPath());

			return;
		}

		$this->assertInstanceOf(SymfonyResponse::class, $response);
	}

	/**
	 * Ensure a 403 page is displayed for a 403 error
	 */
	public function test403()
	{
		$this->dothetest(403);
	}

	/**
	 * Ensure a 404 page is displayed for a 404 error
	 */
	public function test404()
	{
		$this->dothetest(404);
	}

	/**
	 * Ensure a 405 page is displayed for a 405 error
	 */
	public function test405()
	{
		$this->dothetest(405);
	}

	/**
	 * Ensure a 500 page is displayed for a 500 error
	 */
	public function test500()
	{
		$this->dothetest(500);
	}

	/**
	 * Ensure a page is displayed for a other HTTP errors
	 */
	public function testFallback()
	{
		$this->dothetest();
	}
}
