<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\Kameleoon;

use Kameleoon\Data\CustomData;
use Kameleoon\Data\PageView;
use Kameleoon\Data\UserAgent;
use Kameleoon\KameleoonClient as KameleoonClientInterface;
use Kameleoon\KameleoonClientImpl;
use Kameleoon\Types\Variation;
use Lingoda\KameleoonBundle\DTO\KameleoonFeatureFlagData;
use Lingoda\KameleoonBundle\DTO\KameleoonUserData;
use Lingoda\KameleoonBundle\DTO\KameleoonUserDataSet;
use Lingoda\KameleoonBundle\Enum\KameleoonVariationKeyEnum;
use Symfony\Component\HttpFoundation\RequestStack;

class KameleoonFeatureProvider
{
    private const USER_AGENT_HEADER_NAME = 'User-Agent';
    private const CONSENT_COOKIE_NAME = 'OptanonConsent';

    public function __construct(
        private readonly KameleoonClientInterface $client,
        private readonly RequestStack $requestStack,
        private readonly KameleoonConfig $config,
    ) {
    }

    /**
     * visitorCode should be a uniq string that would be identified with a user before AND after creation
     */
    public function getFeatureVariationValue(string $visitorCode, string $featureKey, ?KameleoonUserDataSet $customDataset = null): KameleoonVariationKeyEnum
    {
        return KameleoonVariationKeyEnum::from($this->getVariation($visitorCode, $featureKey, $customDataset)->key);
    }

    /**
     * visitorCode should be a uniq string that would be identified with a user before AND after creation
     */
    public function isFeatureVariant(string $visitorCode, string $featureKey, ?KameleoonUserDataSet $customDataset = null): bool
    {
        $variationValue = KameleoonVariationKeyEnum::from($this->getVariation($visitorCode, $featureKey, $customDataset)->key);

        return KameleoonVariationKeyEnum::isVariant($variationValue);
    }


    /**
     * @return array<string, Variation>
     */
    public function getActiveFeatures(string $visitorCode): array
    {
        return $this->client->getVariations($visitorCode, true, false);
    }

    private function getVariation(string $visitorCode, string $featureKey, ?KameleoonUserDataSet $customDataset = null): Variation
    {

        if ($this->hasConsentAcceptedCookie()) {
            $this->setLegalConsent($visitorCode, true);
        }

        $userAgent = $this->getUserAgent();
        if ($userAgent) {
            $this->client->addData($visitorCode, new UserAgent($userAgent));
        }

        if (null !== $customDataset) {
            $this->addCustomDataSet($visitorCode, $customDataset);
        }

        $currentRequest = $this->requestStack->getCurrentRequest();
        if (null !== $currentRequest) {
            $url = $currentRequest->headers->get('referer') ?? $currentRequest->getUri();
            $this->addPageView($visitorCode, $url, 'No title, server request');
        }

        return $this->client->getVariation($visitorCode, $featureKey);
    }

    /**
     * a function to manually set the legal consent for a visitor
     */
    public function setLegalConsent(string $visitorCode, bool $consent): void
    {
        $this->client->setLegalConsent($visitorCode, $consent);
    }

    /**
     * a function to track kameleoon goal with or without a custom data
     * visitorCode should be a uniq string that would be identified with a user before AND after creation
     */
    public function trackGoal(string $visitorCode, int $goalId, ?KameleoonUserDataSet $customDataset = null): void
    {
        if (null !== $customDataset) {
            $this->addCustomDataSet($visitorCode, $customDataset);
        }

        $this->client->trackConversion($visitorCode, $goalId);
    }


    /**
     * a function to return all the feature flags keys
     * @return string[]
     */
    public function getFeatureKeys(): array
    {
        return $this->client->getFeatureList();
    }

    /**
     * This function parses the loaded Kameleoon server config and takes experiments data
     * @return KameleoonFeatureFlagData[]
     * @throws \RuntimeException
     */
    public function getFeaturesData(): array
    {
        return $this->getKameleoonFeaturesConfig();
    }


    public function addCustomData(string $visitorCode, KameleoonUserData $data): void
    {
        $this->client->addData($visitorCode, new CustomData($data->index, $data->value));
        $this->client->flush($visitorCode);
    }


    public function addCustomDataSet(string $visitorCode, KameleoonUserDataSet $dataSet): void
    {
        foreach ($dataSet->getDataSet() as $data) {
            $this->client->addData(
                $visitorCode,
                new CustomData($data->index, $data->value)
            );
        }
        $this->client->flush($visitorCode);
    }

    /**
     * @param ?string[] $referrers
     */
    private function addPageView(string $visitorCode, string $url, ?string $title, ?array $referrers = null): void
    {
        $this->client->addData($visitorCode, new PageView($url, $title, $referrers));
        $this->client->flush($visitorCode);
    }

    public function getVisitorCodeFromCookies(): string
    {
        return $this->client->getVisitorCode();
    }

    private function getUserAgent(): ?string
    {
        return $this->requestStack->getCurrentRequest()?->headers->get(self::USER_AGENT_HEADER_NAME);
    }

    private function hasConsentAcceptedCookie(): bool
    {
        $consentValue = $this->requestStack->getCurrentRequest()?->cookies->get(self::CONSENT_COOKIE_NAME);

        if (!is_string($consentValue)) {
            return false;
        }

        foreach (explode('&', $consentValue) as $param) {
            if (str_starts_with($param, 'groups=') && str_contains($param, 'C0002:1')) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return KameleoonFeatureFlagData[]
     * @throws \RuntimeException
     */
    private function getKameleoonFeaturesConfig(): array
    {
        $jsonFile = $this->config->getConfigurationFilePath();

        if (!file_exists($jsonFile)) {
            throw new \RuntimeException("Kameleoon config file is not found: {$jsonFile}");
        }

        $jsonContent = file_get_contents($jsonFile);
        if (false === $jsonContent) {
            throw new \RuntimeException("Failed to read Kameleoon config file: {$jsonFile}");
        }

        $configData = json_decode($jsonContent, true);

        if (null === $configData || !is_array($configData)) {
            throw new \RuntimeException("Failed to parse Kameleoon config file: {$jsonFile}");
        }

        $featureFlags = $configData['featureFlags'] ?? [];

        if (!is_array($featureFlags)) {
            throw new \RuntimeException("Feature flags data is not an array in Kameleoon config file: {$jsonFile}");
        }

        return array_map(fn(array $item) =>
            new KameleoonFeatureFlagData(
                $item['id'],
                $item['featureKey'],
                $item['environmentEnabled'],
            ), $featureFlags);
    }
}
