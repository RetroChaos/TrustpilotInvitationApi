<?php

namespace Trustpilot\Test\Api\Invitation;

use PHPUnit\Framework\TestCase;
use Trustpilot\Api\Invitation\Context;

class ContextTest extends TestCase
{
	public function testConstructorSetsAllProperties(): void
	{
		$context = new Context(
			'bu-123',
			'tpl-456',
			'https://example.com/redirect',
			['tag1', 'tag2'],
			'en-GB'
		);

		$this->assertSame('bu-123', $context->getBusinessUnitId());
		$this->assertSame('tpl-456', $context->getTemplateId());
		$this->assertSame('https://example.com/redirect', $context->getRedirectUri());
		$this->assertSame(['tag1', 'tag2'], $context->getTags());
		$this->assertSame('en-GB', $context->getLocale());
	}

	public function testTagsDefaultsToEmptyArrayWhenNull(): void
	{
		$context = new Context(
			'bu-123',
			'tpl-456',
			'https://example.com/redirect',
			null
		);

		$this->assertSame([], $context->getTags());
	}

	public function testLocaleDefaultsToEnUs(): void
	{
		$context = new Context(
			'bu-123',
			'tpl-456',
			'https://example.com/redirect'
		);

		$this->assertSame('en-US', $context->getLocale());
	}
}
