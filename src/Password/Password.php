<?php

namespace SimpleTicketing\Password;

final class Password
{
    /** @var string */
    private $password;

    /** @var string */
    private $hash;

    /**
     * @param string $password
     */
    public function __construct(string $password)
    {
        $this->password = $password;
        $this->hash = password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     *
     * @return bool
     */
    public function validateAgainstHash(string $hash): bool
    {
        return password_verify($this->password, $hash);
    }
}
