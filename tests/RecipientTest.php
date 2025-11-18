<?php

namespace Trustpilot\Test\Api\Invitation;

use PHPUnit\Framework\TestCase;
use Trustpilot\Api\Invitation\Recipient;

class RecipientTest extends TestCase
{
	public function testConstructorSetsProperties(): void
	{
		$recipient = new Recipient('customer@example.com', 'Customer Name');

		$this->assertSame('customer@example.com', $recipient->getEmail());
		$this->assertSame('Customer Name', $recipient->getName());
	}

	public function testEmailIsString(): void
	{
		$recipient = new Recipient('foo@bar.com', 'Foo Bar');
		$this->assertIsString($recipient->getEmail());
	}

	public function testNameIsString(): void
	{
		$recipient = new Recipient('foo@bar.com', 'Foo Bar');
		$this->assertIsString($recipient->getName());
	}
}
