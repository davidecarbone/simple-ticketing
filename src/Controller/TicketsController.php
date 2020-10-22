<?php

namespace SimpleTicketing\Controller;

use SimpleTicketing\Authentication\JWT;
use SimpleTicketing\Repository\TicketRepository;
use SimpleTicketing\Ticket\Ticket;
use SimpleTicketing\Ticket\TicketMessage;
use SimpleTicketing\User\User;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TicketsController implements TokenAuthenticatedController
{
	/** @var TicketRepository */
	private $ticketRepository;

	/** @var JWT */
	private $jwt;

	/**
	 * @param TicketRepository $ticketRepository
	 * @param JWT              $jwt
	 */
	public function __construct(TicketRepository $ticketRepository, JWT $jwt)
	{
		$this->ticketRepository = $ticketRepository;
		$this->jwt = $jwt;
	}

	/**
	 * @param Request $request
	 *
	 * @return JsonResponse
	 */
	public function getTickets(Request $request): JsonResponse
	{
		$user = $this->retrieveUserFromRequest($request);
		$tickets = $this->ticketRepository->findByUserId($user->id());

		return new JsonResponse($tickets, Response::HTTP_OK);
	}

	/**
	 * @param Request $request
	 *
	 * @return JsonResponse
	 */
	public function postTicket(Request $request): JsonResponse
	{
		$requestContent = json_decode($request->getContent(), true);
		$user = $this->retrieveUserFromRequest($request);
		$message = $requestContent['message'] ?? null;

		try {
			$this->assertRequestIsValid($request);

			$ticket = Ticket::createWithAuthorIdAndMessage($user->id(), new TicketMessage($message));

			$this->ticketRepository->insert($ticket);
		} catch (BadRequestException | \InvalidArgumentException $exception) {
			return new JsonResponse([
				'error' => $exception->getMessage()
			], Response::HTTP_BAD_REQUEST);
		}

		return new JsonResponse(['message' => 'Ticket successfully created.'], Response::HTTP_CREATED);
	}

	/**
	 * @param Request $request
	 *
	 * @throws BadRequestException
	 */
	private function assertRequestIsValid(Request $request)
	{
		$requestContent = json_decode($request->getContent(), true);

		if (empty($requestContent['message'])) {
			throw new BadRequestException('A message is required.');
		}
	}

	/**
	 * @param Request $request
	 *
	 * @return User
	 */
	private function retrieveUserFromRequest(Request $request): User
	{
		$jwtToken = $request->headers->get('JWT');
		$userData = json_decode(json_encode($this->jwt->decode($jwtToken)), true);

		return User::fromArray($userData);
	}
}
