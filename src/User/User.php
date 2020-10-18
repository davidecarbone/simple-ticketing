<?php

namespace SimpleTicketing\User;

use SimpleTicketing\Password\Password;

class User
{
    /** @var UserId */
    private $id;

    /** @var string */
    private $username;

    /** @var Password */
    private $password;

	/** @var string */
    private $fullName;

	private function __construct()
	{
	}

    /**
     * @param array $data
     *
     * @return User
     */
    public static function fromArray(array $data): User
    {
        $user = new self();

        $user->id = new UserId($data['id']);
        $user->username = $data['username'] ?? null;
        $user->password = $data['password'] ? new Password($data['password']) : null;
	    $user->fullName = $data['fullName'];

        return $user;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => (string)$this->id,
            'username' => $this->username,
            'password' => $this->password ? $this->password->getHash() : null,
	        'fullName' => $this->fullName
        ];
    }
}
