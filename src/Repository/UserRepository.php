<?php

namespace SimpleTicketing\Repository;

use SimpleTicketing\Password\Password;
use SimpleTicketing\User\User;

class UserRepository extends DBALRepository
{
    private const TABLE_NAME = 'User';

	/**
	 * @param string   $username
	 * @param Password $password
	 *
	 * @return User|null
	 */
	public function findByUsernameAndPassword(string $username, Password $password): ?User
	{
		$userData = $this->findUserDataByUsername($username);

		if (!$userData || !$password->validateAgainstHash($userData['password'])) {
			return null;
		};

		return User::fromArray($userData);
	}

	/**
	 * @param string $username
	 */
	private function findUserDataByUsername(string $username): array
	{
		$stmt = $this->connection->createQueryBuilder()
			->select('id', 'fullName', 'type', 'password')
			->from(self::TABLE_NAME)
			->where('username = ?')
			->setParameter(0, $username)
			->execute();

		return $stmt->fetch();
	}
}
