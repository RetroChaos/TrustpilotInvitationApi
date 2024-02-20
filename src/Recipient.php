<?php

namespace Trustpilot\Api\Invitation;

class Recipient {
    /**
     * @var string
     */
    private string $email;

    /**
     * @var string
     */
    private string $name;

    /**
     * @param string $email
     * @param string $name
     */
    public function __construct(string $email, string $name)
    {
        $this->email = $email;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getEmail(): string {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }
}
