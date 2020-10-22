<?php

namespace SimpleTicketing\Tests\End2End;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use SimpleTicketing\Tests\ContainerAwareTest;
use Symfony\Component\HttpFoundation\Response;

class ProductsTest extends ContainerAwareTest
{
	private const TEST_VALID_JWT = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6ImRmZDMyY2JmLWRlMmYtNDYxNi05MGI2LTI3ZmM5MDYzMjk4MyIsInVzZXJuYW1lIjpudWxsLCJwYXNzd29yZCI6IiQyeSQxMCQwakNVXC8uQm41akZBay5tRjBOSGoxdVwvVkZNNE5OLnpPT0VmVzR6TktyRUFsbzNqVm9jT0o2IiwidHlwZSI6IkFETUlOIiwiZnVsbE5hbWUiOiJUZXN0IiwiZXhwIjoxNjAzOTU5NTMyfQ.KHUudgdK6vTizLtKSC38Ey7LL1YouD3_1IZ9eZZ8_X4';
	private const TEST_INVALID_JWT = 'ajskhf.eyJpZCI6IjRkOGYzOGRjLTA1ZDQtNDJhNi05M2ZlLTY5YTcyZmM1MzNiMSIsInVzZXJuYW1lIjoiYWRtaW4iLCJwYXNzd29yZCI6IiQyeSQxMCRBeTcxZEJuSEtCTkVFYkp1MTJFN1R1b2J2WUplM0VPR3d3OWhFMUZUSHdPVlVrZW50ZEY0UyIsImV4cCI6MTU5NTE0OTM1Mn0.LtcHTIYgTuRa8oZizLuqYg5RhXTZHrE8ns7JehCQDv4';

	/** @var Client */
    private $client;

    public function setUp()
    {
        parent::setUp();
	    $this->client = $client = new Client(['base_uri' => 'http://localhost:8080/']);
    }

    /** @test */
    public function get_tickets_should_respond_200_with_an_array_of_tickets()
    {
        $response = $this->client->get('tickets', [
            'headers' => ['JWT' => self::TEST_VALID_JWT],
		]);
        $responseBody = json_decode($response->getBody(), true);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertIsArray($responseBody);
	    $this->assertArrayHasKey('id', $responseBody[0]);
	    $this->assertArrayHasKey('assignedTo', $responseBody[0]);
	    $this->assertArrayHasKey('status', $responseBody[0]);
	    $this->assertArrayHasKey('messages', $responseBody[0]);
	    $this->assertArrayHasKey('body', $responseBody[0]['messages'][0]);
	    $this->assertArrayHasKey('authorId', $responseBody[0]['messages'][0]);
	    $this->assertArrayHasKey('createdOn', $responseBody[0]);
	    $this->assertArrayHasKey('updatedOn', $responseBody[0]);
    }

	/** @test */
	public function get_tickets_should_respond_401_with_an_error_when_jwt_is_not_provided()
	{
		$statusCode = null;
		$responseBody = [];

		try {
			$this->client->get('tickets');
		} catch (ClientException $e) {
			if ($e->hasResponse()) {
				$statusCode = $e->getResponse()->getStatusCode();
				$responseBody = json_decode($e->getResponse()->getBody(), true);
			}
		}

		$this->assertEquals(Response::HTTP_UNAUTHORIZED, $statusCode);
		$this->assertArrayHasKey('error', $responseBody);
	}

	/** @test */
	public function get_tickets_id_should_respond_200_with_a_ticket()
	{
		$response = $this->client->get('tickets/70d48ec5-ceca-49b8-95e9-4f7ceae20451', [
			'headers' => ['JWT' => self::TEST_VALID_JWT],
		]);
		$responseBody = json_decode($response->getBody(), true);

		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$this->assertArrayHasKey('id', $responseBody);
		$this->assertArrayHasKey('assignedTo', $responseBody);
		$this->assertArrayHasKey('status', $responseBody);
		$this->assertArrayHasKey('messages', $responseBody);
		$this->assertArrayHasKey('body', $responseBody['messages'][0]);
		$this->assertArrayHasKey('authorId', $responseBody['messages'][0]);
		$this->assertArrayHasKey('createdOn', $responseBody);
		$this->assertArrayHasKey('updatedOn', $responseBody);
	}

	/** @test */
	public function get_tickets_id_should_respond_400_with_an_error_when_ticket_id_is_malformed()
	{
		$statusCode = null;
		$responseBody = [];

		try {
			$this->client->get('tickets/123', [
				'headers' => ['JWT' => self::TEST_VALID_JWT],
			]);
		} catch (ClientException $e) {
			if ($e->hasResponse()) {
				$statusCode = $e->getResponse()->getStatusCode();
				$responseBody = json_decode($e->getResponse()->getBody(), true);
			}
		}

		$this->assertEquals(Response::HTTP_BAD_REQUEST, $statusCode);
		$this->assertArrayHasKey('error', $responseBody);
	}

	/** @test */
	public function get_tickets_id_should_respond_401_with_an_error_when_jwt_is_not_provided()
	{
		$statusCode = null;
		$responseBody = [];

		try {
			$this->client->get('tickets/70d48ec5-ceca-49b8-95e9-4f7ceae20452');
		} catch (ClientException $e) {
			if ($e->hasResponse()) {
				$statusCode = $e->getResponse()->getStatusCode();
				$responseBody = json_decode($e->getResponse()->getBody(), true);
			}
		}

		$this->assertEquals(Response::HTTP_UNAUTHORIZED, $statusCode);
		$this->assertArrayHasKey('error', $responseBody);
	}

	/** @test */
	public function get_tickets_id_should_respond_404_with_an_error_when_a_ticket_is_not_found()
	{
		$statusCode = null;
		$responseBody = [];

		try {
			$this->client->get('tickets/70d48ec5-ceca-49b8-95e9-4f7ceae20452', [
				'headers' => ['JWT' => self::TEST_VALID_JWT],
			]);
		} catch (ClientException $e) {
			if ($e->hasResponse()) {
				$statusCode = $e->getResponse()->getStatusCode();
				$responseBody = json_decode($e->getResponse()->getBody(), true);
			}
		}

		$this->assertEquals(Response::HTTP_NOT_FOUND, $statusCode);
		$this->assertArrayHasKey('error', $responseBody);
	}
}
