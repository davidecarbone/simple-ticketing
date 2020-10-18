<?php

namespace SimpleTicketing\EventSubscriber;

use DomainException;
use Firebase\JWT\SignatureInvalidException;
use SimpleTicketing\Authentication\AuthenticationException;
use SimpleTicketing\Authentication\JWT;
use SimpleTicketing\Controller\TokenAuthenticatedController;
use SimpleTicketing\User\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use UnexpectedValueException;

class TokenSubscriber implements EventSubscriberInterface
{
	/* @var JWT */
	private $jwt;

	/**
	 * @param JWT $jwt
	 */
	public function __construct(JWT $jwt)
	{
		$this->jwt = $jwt;
	}

	/**
	 * @param ControllerEvent $event
	 *
	 * @throws AuthenticationException
	 */
	public function onKernelController(ControllerEvent $event)
	{
		$controller = $event->getController();

		// when a controller class defines multiple action methods, the controller
		// is returned as [$controllerInstance, 'methodName']
		if (is_array($controller)) {
			$controller = $controller[0];
		}

		if ($controller instanceof TokenAuthenticatedController) {
			$token = $event->getRequest()->headers->get('JWT');

			if (!$this->tokenIsValid($token)) {
				$message = 'You are not authorized to access this resource.';
				$event->setController(
					function() use ($message) {
						return new JsonResponse([
							'error' => $message
						], Response::HTTP_UNAUTHORIZED);
					}
				);
			}
		}
	}

	public static function getSubscribedEvents()
	{
		return [
			KernelEvents::CONTROLLER => 'onKernelController'
		];
	}

	/**
	 * @param string|null $token
	 *
	 * @return bool
	 */
	private function tokenIsValid(?string $token): bool
	{
		if (empty($token)) {
			return false;
		}

		try {
			$userData = json_decode(json_encode($this->jwt->decode($token)), true);
		} catch (DomainException | SignatureInvalidException | UnexpectedValueException $e) {
			return false;
		}

		$user = User::fromArray($userData);

		return ($user instanceof User);
	}
}
