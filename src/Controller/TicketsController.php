<?php

namespace App\Controller;

use App\Ticket\TicketRepository;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TicketsController
{
	private $ticketRepository;

	public function __construct(TicketRepository $ticketRepository)
	{
		$this->ticketRepository = $ticketRepository;
	}

	public function add(Request $request): JsonResponse
	{
		$data = json_decode($request->getContent(), true);

		$authorId = $data['authorId'];

		if (empty($authorId)) {
			throw new BadRequestException('Expecting mandatory parameters!');
		}

		$this->ticketRepository->save($authorId);

		return new JsonResponse(['status' => 'Ticket created!'], Response::HTTP_CREATED);
	}
}
