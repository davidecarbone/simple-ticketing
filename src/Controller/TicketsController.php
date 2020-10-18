<?php

namespace SimpleTicketing\Controller;

use SimpleTicketing\Repository\TicketRepository;
use SimpleTicketing\Ticket\Ticket;
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

		if (empty($requestContent['authorId'])) {
			throw new BadRequestException('Expecting mandatory parameters!');
		}

		$ticket = Ticket::createWithAuthorId($requestContent['authorId']);

		$this->ticketRepository->save($ticket);

		return new JsonResponse(['message' => 'Ticket created!'], Response::HTTP_CREATED);
	}
}
