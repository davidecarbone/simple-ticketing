<?php

namespace SimpleTicketing\Authentication;

use DateInterval;
use DateTime;
use Firebase\JWT\JWT as FireBaseJWT;

class JWT
{
    /** @var $secret */
    private $secret;

    /** @var DateInterval */
    private $validityInterval;


    /**
     * @param string $secret
     * @param DateInterval $validityInterval
     */
    public function __construct(string $secret, DateInterval $validityInterval = null)
    {
        if ($validityInterval === null) {
            $validityInterval = new DateInterval('P7D');
        }

        $this->secret = $secret;
        $this->validityInterval = $validityInterval;
    }

    /**
     * @param array $payload
     * @param DateTime|null $expirationDateTime
     *
     * @return string
     */
    public function encode(array $payload, DateTime $expirationDateTime = null)
    {
        if ($expirationDateTime === null) {
            $now = new DateTime('now');
            $now->add($this->validityInterval);
            $expirationDateTime = $now;
        }

        $payload['exp'] = $expirationDateTime->getTimestamp();

        return FireBaseJWT::encode($payload, $this->secret);
    }

    /**
     * @param string $token
     *
     * @return object
     */
    public function decode($token)
    {
        return FireBaseJWT::decode((string)$token, $this->secret, ['HS256']);
    }
}
