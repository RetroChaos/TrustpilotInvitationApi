<?php

namespace Trustpilot\Api\Invitation;

use DateTimeInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Trustpilot\Api\Authenticator\AccessToken;

class Client
{
	public const ENDPOINT = 'https://invitations-api.trustpilot.com/v1/private/business-units/';

	private AccessToken $accessToken;

	private HttpClientInterface $httpClient;

	public function __construct(AccessToken $accessToken, ?HttpClientInterface $httpClient = null)
	{
		$this->accessToken = $accessToken;
		$this->httpClient = $httpClient ?? HttpClient::create();
	}

	/**
	 * Internal HTTP wrapper.
	 * @throws InvitationException
	 */
	private function makeRequest(string $url, array $json = null): array
	{
		$method  = 'GET';
		$options = [
			'query' => [
				'token' => $this->accessToken->getToken(),
			],
		];

		if ($json !== null) {
			$method = 'POST';
			$options['json'] = $json;
		}

		try {
			$response = $this->httpClient->request($method, $url, $options);
			$status = $response->getStatusCode();
		} catch (TransportExceptionInterface $e) {
			throw new InvitationException(
				'Network error calling Trustpilot Invitation API: ' . $e->getMessage(),
				0,
				$e
			);
		}

		// Read body via stream (no exceptions, works for all statuses)
		$raw = (string)'';
		foreach ($this->httpClient->stream($response) as $chunk) {
			try {
				$raw .= $chunk->getContent();
			} catch (TransportExceptionInterface $e) {
				throw new InvitationException(
					'Network error while steaming content: ' . $e->getMessage(),
					0,
					$e
				);
			}
		}

		if ($status >= 400) {
			throw new InvitationException(
				sprintf("Trustpilot Invitation API returned HTTP %d", $status),
				$status
			);
		}

		$data = json_decode($raw, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new InvitationException(
				'Failed to decode Trustpilot Invitation API JSON: ' . json_last_error_msg()
			);
		}

		if (!is_array($data)) {
			throw new InvitationException(
				'Unexpected response format from Trustpilot Invitation API.'
			);
		}

		return $data;
	}

	/**
	 * Send an invitation.
	 * @throws InvitationException
	 */
	public function invite(
		Context $context,
		Recipient $recipient,
		Sender $sender,
		string $referenceId,
		DateTimeInterface $time = null
	): array {
		if ($time === null) {
			$time = new \DateTimeImmutable();
		}

		$json = [
			'recipientEmail'     => $recipient->getEmail(),
			'recipientName'      => $recipient->getName(),
			'referenceId'        => $referenceId,
			'templateId'         => $context->getTemplateId(),
			'locale'             => $context->getLocale(),
			'senderName'         => $sender->getName(),
			'senderEmail'        => $sender->getEmail(),
			'replyTo'            => $sender->getReplyEmail(),
			'preferredSendTime'  => $time->format('c'),
			'tags'               => $context->getTags(),
			'redirectUri'        => $context->getRedirectUri(),
		];

		$url = self::ENDPOINT . $context->getBusinessUnitId() . '/invitations';

		return $this->makeRequest($url, $json);
	}
}
