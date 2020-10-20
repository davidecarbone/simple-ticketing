<?php

namespace SimpleTicketing\Controller;

use SimpleTicketing\Repository\TicketRepository;
use SimpleTicketing\Ticket\Ticket;
use SimpleTicketing\Ticket\TicketMessage;
use SimpleTicketing\User\UserId;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TicketsController implements TokenAuthenticatedController
{
	/** @var TicketRepository */
	private $ticketRepository;

	/**
	 * @param TicketRepository $ticketRepository
	 */
	public function __construct(TicketRepository $ticketRepository)
	{
		$this->ticketRepository = $ticketRepository;
	}

	/**
	 * @param Request $request
	 *
	 * @return JsonResponse
	 */
	public function postTicket(Request $request): JsonResponse
	{
		$requestContent = json_decode($request->getContent(), true);
		$authorId = $requestContent['authorId'] ?? null;
		$message = $requestContent['message'] ?? null;

		try {
			$this->assertRequestIsValid($request);

			$ticket = Ticket::createWithAuthorIdAndMessage(new UserId($authorId), new TicketMessage($message));

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

		if (empty($requestContent['authorId']) || empty($requestContent['message'])) {
			throw new BadRequestException('AuthorId and message are required.');
		}
	}
}
