<?php

namespace Trustpilot\Api\Invitation;

class Sender {
    /**
     * @var string
     */
    private string $email;

    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $replyEmail;

    /**
     * @param string $email
     * @param string $name
     * @param string $replyEmail
     */
    public function __construct(string $email, string $name, string $replyEmail)
    {
        $this->email = $email;
        $this->name = $name;
        $this->replyEmail = $replyEmail;
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

    /**
     * @return string
     */
    public function getReplyEmail(): string {
        return $this->replyEmail;
    }
}
