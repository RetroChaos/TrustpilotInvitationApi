<?php

namespace Trustpilot\Api\Invitation;

class Context {
    /**
     * @var string
     */
    private string $businessUnitId;

    /**
     * @var string
     */
    private string $templateId;

    /**
     * @var string
     */
    private string $redirectUri;

    /**
     * @var string[]
     */
    private array $tags;

    /**
     * @var string
     */
    private string $locale;

    /**
     * @param string $businessUnitId
     * @param string $templateId
     * @param string $redirectUri
     * @param string[] $tags
     * @param string $locale
     */
    public function __construct(string $businessUnitId, string $templateId, string $redirectUri, array $tags = null, string $locale = 'en-US') {
        $this->businessUnitId = $businessUnitId;
        $this->templateId = $templateId;
        $this->redirectUri = $redirectUri;
        $this->tags = (null === $tags) ? [] : $tags;
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getBusinessUnitId(): string {
        return $this->businessUnitId;
    }

    /**
     * @return string
     */
    public function getTemplateId(): string {
        return $this->templateId;
    }

    /**
     * @return string
     */
    public function getRedirectUri(): string {
        return $this->redirectUri;
    }

    /**
     * @return string[]
     */
    public function getTags(): array {
        return $this->tags;
    }

    /**
     * @return string
     */
    public function getLocale(): string {
        return $this->locale;
    }
}
