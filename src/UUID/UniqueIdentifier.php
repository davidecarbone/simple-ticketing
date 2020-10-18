<?php

namespace SimpleTicketing\UUID;

use InvalidArgumentException;

abstract class UniqueIdentifier
{
    /** @var string */
    protected $id;

    public function __construct(string $id = null)
    {
        if (null === $id) {
            $id = UUID::v4();
        }

        if (!UUID::validate($id)) {
            throw new InvalidArgumentException('Invalid UUID: ' . $id);
        }

        $this->id = $id;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->id;
    }
}
