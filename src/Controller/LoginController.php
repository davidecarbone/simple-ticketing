<?php

namespace SimpleTicketing\Controller;

use SimpleTicketing\Authentication\AuthenticationException;
use SimpleTicketing\Authentication\JWT;
use SimpleTicketing\Password\Password;
use SimpleTicketing\Repository\UserRepository;
use SimpleTicketing\User\User;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginController
{
    /** @var JWT */
    private $jwt;

    /** @var UserRepository */
    private $userRepository;

    /**
     * @param JWT            $jwt
     * @param UserRepository $userRepository
     */
    public function __construct(JWT $jwt, UserRepository $userRepository)
    {
        $this->jwt = $jwt;
        $this->userRepository = $userRepository;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function login(Request $request): JsonResponse
    {
	    $requestContent = json_decode($request->getContent(), true);
        $usernameFromRequest = $requestContent['username'] ?? null;
        $passwordFromRequest = $requestContent['password'] ?? null;

        try {
            $this->assertRequestIsValid($request);

            $password = new Password($passwordFromRequest);
            $user = $this->userRepository->findByUsernameAndPassword($usernameFromRequest, $password);

            $this->assertUserIsValid($user);

        } catch (BadRequestException $exception) {
            return new JsonResponse([
                'error' => $exception->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (AuthenticationException $exception) {
            return new JsonResponse([
                'error' => $exception->getMessage()
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $this->jwt->encode($user->toArray());

        return new JsonResponse([
            'JWT' => $token
        ], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     *
     * @throws BadRequestException
     */
    private function assertRequestIsValid(Request $request)
    {
	    $requestContent = json_decode($request->getContent(), true);

        if (empty($requestContent['username']) || empty($requestContent['password'])) {
            throw new BadRequestException('Username and password are required.');
        }
    }

    /**
     * @param User|null $user
     *
     * @throws AuthenticationException
     */
    private function assertUserIsValid(?User $user)
    {
        if (null === $user) {
            throw new AuthenticationException('Username and password are not valid.');
        }
    }
}
