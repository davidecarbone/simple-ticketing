<?php

namespace SimpleTicketing\User;

final class UserType
{
	public const ADMIN = 'ADMIN';
	public const CUSTOMER = 'CUSTOMER';

	/** @var string */
	private $userType;

	/**
	 * @param string $userType
	 *
	 * @throws UserTypeException
	 */
	public function __construct(string $userType)
	{
		if ($userType != self::ADMIN && $userType != self::CUSTOMER) {
			throw new UserTypeException("$userType is not a valid user type.");
		}

		$this->userType = $userType;
	}

	public function __toString()
	{
		return $this->userType;
	}
}
