<?php

namespace Trustpilot\Test\Api\Invitation;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Trustpilot\Api\Authenticator\AccessToken;
use Trustpilot\Api\Invitation\Client;
use Trustpilot\Api\Invitation\Context;
use Trustpilot\Api\Invitation\InvitationException;
use Trustpilot\Api\Invitation\Recipient;
use Trustpilot\Api\Invitation\Sender;

class ClientTest extends TestCase
{
	private function createAccessToken(): AccessToken
	{
		$expiry = new \DateTimeImmutable('+1 hour');

		return new AccessToken('test-access-token', $expiry, null);
	}

	/**
	 * @throws InvitationException
	 */
	public function testInviteSuccessBuildsCorrectRequest(): void
	{
		$captured = [
			'method' => null,
			'url' => null,
			'options' => null,
		];

		$mockClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
			$captured['method'] = $method;
			$captured['url'] = $url;
			$captured['options'] = $options;

			return new MockResponse(json_encode(['result' => 'ok'], JSON_THROW_ON_ERROR), [
				'http_code' => 200,
			]);
		});

		$client = new Client($this->createAccessToken(), $mockClient);

		// Use mocks for Context / Recipient / Sender so we don't rely on their real implementations
		$context = $this->createMock(Context::class);
		$context->method('getBusinessUnitId')->willReturn('bu-123');
		$context->method('getTemplateId')->willReturn('tpl-456');
		$context->method('getLocale')->willReturn('en-GB');
		$context->method('getTags')->willReturn(['tag1', 'tag2']);
		$context->method('getRedirectUri')->willReturn('https://example.com/thank-you');

		$recipient = $this->createMock(Recipient::class);
		$recipient->method('getEmail')->willReturn('customer@example.com');
		$recipient->method('getName')->willReturn('Customer Name');

		$sender = $this->createMock(Sender::class);
		$sender->method('getEmail')->willReturn('noreply@example.com');
		$sender->method('getName')->willReturn('Shop Name');
		$sender->method('getReplyEmail')->willReturn('support@example.com');

		$time = new \DateTimeImmutable('2025-01-01T12:00:00+00:00');

		$result = $client->invite($context, $recipient, $sender, 'REF-123', $time);

		// Response assertion
		$this->assertSame(['result' => 'ok'], $result);

		// Request assertions
		$this->assertSame('POST', $captured['method']);

		// URL will include query string, so just check the base path
		$this->assertStringStartsWith(
			Client::ENDPOINT . 'bu-123/invitations',
			$captured['url']
		);

		$options = $captured['options'];

		// Ensure token is in query
		$this->assertArrayHasKey('query', $options);
		$this->assertSame('test-access-token', $options['query']['token']);

		// Symfony normalises "json" into "body" before it reaches MockHttpClient,
		// so we inspect the "body" option here.
		$this->assertArrayHasKey('body', $options);
		$payload = $options['body'];

		if (is_string($payload)) {
			$json = json_decode($payload, true);
			$this->assertSame(JSON_ERROR_NONE, json_last_error(), 'Body is not valid JSON');
		} else {
			$json = $payload;
		}

		$this->assertSame('customer@example.com', $json['recipientEmail']);
		$this->assertSame('Customer Name', $json['recipientName']);
		$this->assertSame('REF-123', $json['referenceId']);
		$this->assertSame('tpl-456', $json['templateId']);
		$this->assertSame('en-GB', $json['locale']);
		$this->assertSame('Shop Name', $json['senderName']);
		$this->assertSame('noreply@example.com', $json['senderEmail']);
		$this->assertSame('support@example.com', $json['replyTo']);
		$this->assertSame($time->format('c'), $json['preferredSendTime']);
		$this->assertSame(['tag1', 'tag2'], $json['tags']);
		$this->assertSame('https://example.com/thank-you', $json['redirectUri']);
	}

	public function testHttpErrorThrowsInvitationException(): void
	{
		$mockClient = new MockHttpClient(
			new MockResponse('{"error":"bad request"}', ['http_code' => 400])
		);

		$client = new Client($this->createAccessToken(), $mockClient);

		$context = $this->createMock(Context::class);
		$context->method('getBusinessUnitId')->willReturn('bu-123');
		$context->method('getTemplateId')->willReturn('tpl-456');
		$context->method('getLocale')->willReturn('en-GB');
		$context->method('getTags')->willReturn([]);
		$context->method('getRedirectUri')->willReturn('https://example.com/thank-you');

		$recipient = $this->createMock(Recipient::class);
		$recipient->method('getEmail')->willReturn('customer@example.com');
		$recipient->method('getName')->willReturn('Customer Name');

		$sender = $this->createMock(Sender::class);
		$sender->method('getEmail')->willReturn('noreply@example.com');
		$sender->method('getName')->willReturn('Shop Name');
		$sender->method('getReplyEmail')->willReturn('support@example.com');

		$this->expectException(InvitationException::class);
		$this->expectExceptionCode(400);
		$this->expectExceptionMessage('Trustpilot Invitation API returned HTTP 400');

		$client->invite($context, $recipient, $sender, 'REF-ERR');
	}

	public function testInvalidJsonThrowsInvitationException(): void
	{
		$mockClient = new MockHttpClient(
			new MockResponse('not-json', ['http_code' => 200])
		);

		$client = new Client($this->createAccessToken(), $mockClient);

		$context = $this->createMock(Context::class);
		$context->method('getBusinessUnitId')->willReturn('bu-123');
		$context->method('getTemplateId')->willReturn('tpl-456');
		$context->method('getLocale')->willReturn('en-GB');
		$context->method('getTags')->willReturn([]);
		$context->method('getRedirectUri')->willReturn('https://example.com/thank-you');

		$recipient = $this->createMock(Recipient::class);
		$recipient->method('getEmail')->willReturn('customer@example.com');
		$recipient->method('getName')->willReturn('Customer Name');

		$sender = $this->createMock(Sender::class);
		$sender->method('getEmail')->willReturn('noreply@example.com');
		$sender->method('getName')->willReturn('Shop Name');
		$sender->method('getReplyEmail')->willReturn('support@example.com');

		$this->expectException(InvitationException::class);
		$this->expectExceptionMessageMatches(
			'/Failed to decode Trustpilot Invitation API JSON:/'
		);

		$client->invite($context, $recipient, $sender, 'REF-JSON');
	}

	public function testNetworkErrorIsWrappedInInvitationException(): void
	{
		$mockClient = new MockHttpClient(function () {
			throw new TransportException('Connection failed');
		});

		$client = new Client($this->createAccessToken(), $mockClient);

		$context = $this->createMock(Context::class);
		$context->method('getBusinessUnitId')->willReturn('bu-123');
		$context->method('getTemplateId')->willReturn('tpl-456');
		$context->method('getLocale')->willReturn('en-GB');
		$context->method('getTags')->willReturn([]);
		$context->method('getRedirectUri')->willReturn('https://example.com/thank-you');

		$recipient = $this->createMock(Recipient::class);
		$recipient->method('getEmail')->willReturn('customer@example.com');
		$recipient->method('getName')->willReturn('Customer Name');

		$sender = $this->createMock(Sender::class);
		$sender->method('getEmail')->willReturn('noreply@example.com');
		$sender->method('getName')->willReturn('Shop Name');
		$sender->method('getReplyEmail')->willReturn('support@example.com');

		$this->expectException(InvitationException::class);
		$this->expectExceptionMessage(
			'Network error calling Trustpilot Invitation API: Connection failed'
		);

		$client->invite($context, $recipient, $sender, 'REF-NET');
	}
}
