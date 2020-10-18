<?php

namespace SimpleTicketing\Controller;

use SimpleTicketing\Repository\TicketRepository;
use SimpleTicketing\Ticket\Ticket;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TicketsController
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
		$data = json_decode($request->getContent(), true);

		if (empty($data['authorId'])) {
			throw new BadRequestException('Expecting mandatory parameters!');
		}

		$ticket = Ticket::createWithAuthorId($data['authorId']);

		$this->ticketRepository->save($ticket);

		return new JsonResponse(['message' => 'Ticket created!'], Response::HTTP_CREATED);
	}
}
