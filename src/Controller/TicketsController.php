<?php

namespace SimpleTicketing\Controller;

use SimpleTicketing\Authentication\JWT;
use SimpleTicketing\Repository\TicketRepository;
use SimpleTicketing\Ticket\Ticket;
use SimpleTicketing\Ticket\TicketId;
use SimpleTicketing\Ticket\TicketMessage;
use SimpleTicketing\Ticket\TicketOwnershipException;
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
	public function getTicketById(Request $request): JsonResponse
	{
		$user = $this->retrieveUserFromRequest($request);
		$ticketId = $request->attributes->get('id');

		try {
			$ticket = $this->ticketRepository->findById(new TicketId($ticketId));

			if (!$ticket) {
				return new JsonResponse([
					'error' => 'Resource was not found.'
				], Response::HTTP_NOT_FOUND);
			}

			$this->assertTicketBelongsToUser($ticket, $user);

		} catch (TicketOwnershipException $exception) {
			return new JsonResponse([
				'error' => $exception->getMessage()
			], Response::HTTP_FORBIDDEN);
		} catch (\InvalidArgumentException $exception) {
			return new JsonResponse([
				'error' => 'Invalid ticketId format'
			], Response::HTTP_BAD_REQUEST);
		}

		return new JsonResponse($ticket, Response::HTTP_OK);
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
			$this->assertMessageHasBeenProvided($request);

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
	private function assertMessageHasBeenProvided(Request $request)
	{
		$requestContent = json_decode($request->getContent(), true);

		if (empty($requestContent['message'])) {
			throw new BadRequestException('A message is required.');
		}
	}

	/**
	 * @param Ticket|null $ticket
	 * @param User        $user
	 *
	 * @throws TicketOwnershipException
	 */
	private function assertTicketBelongsToUser(?Ticket $ticket, User $user)
	{
		if (!$ticket->belongsToUser($user)) {
			throw new TicketOwnershipException('You don\'t have the permissions to access this resource.');
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
