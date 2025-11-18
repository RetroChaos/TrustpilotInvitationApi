<?php

namespace Trustpilot\Test\Api\Invitation;

use PHPUnit\Framework\TestCase;
use Trustpilot\Api\Invitation\Sender;

class SenderTest extends TestCase
{
	public function testConstructorSetsProperties(): void
	{
		$sender = new Sender(
			'noreply@example.com',
			'Shop Name',
			'support@example.com'
		);

		$this->assertSame('noreply@example.com', $sender->getEmail());
		$this->assertSame('Shop Name', $sender->getName());
		$this->assertSame('support@example.com', $sender->getReplyEmail());
	}

	public function testEmailIsString(): void
	{
		$sender = new Sender('a@b.com', 'Test', 'reply@b.com');
		$this->assertIsString($sender->getEmail());
	}

	public function testNameIsString(): void
	{
		$sender = new Sender('a@b.com', 'Test', 'reply@b.com');
		$this->assertIsString($sender->getName());
	}

	public function testReplyEmailIsString(): void
	{
		$sender = new Sender('a@b.com', 'Test', 'reply@b.com');
		$this->assertIsString($sender->getReplyEmail());
	}
}
