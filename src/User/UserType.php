<?php

namespace SimpleTicketing\User;

final class UserType
{
	private const TYPE_ADMIN = 'ADMIN';
	private const TYPE_CUSTOMER = 'CUSTOMER';

	/** @var string */
	private $userType;

	/**
	 * @param string $userType
	 *
	 * @throws UserTypeException
	 */
	public function __construct(string $userType)
	{
		if ($userType != self::TYPE_ADMIN && $userType != self::TYPE_CUSTOMER) {
			throw new UserTypeException("$userType is not a valid user type.");
		}

		$this->userType = $userType;
	}

	public function __toString()
	{
		return $this->userType;
	}
}
