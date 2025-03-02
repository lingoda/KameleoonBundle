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
    public function isFeatureActive(string $visitorCode, string $featureKey, ?KameleoonUserDataSet $customDataset = null): bool
    {
        return $this->getVariation($visitorCode, $featureKey, $customDataset)->isActive();
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

        return $this->client->getVariation($visitorCode, $featureKey);
    }

    /**
     * a function to manually set the legal consent for a visitor
     */
    public function setLegalConsent(string $visitorCode, bool $consent): void
    {
        $this->client->setLegalConsent($visitorCode, $consent);
    }

    public function trackGoal(string $visitorCode, int $goalId, ?KameleoonUserDataSet $customDataset = null): void
    {
        if (null !== $customDataset) {
            $this->addCustomDataSet($visitorCode, $customDataset);
        }

        $this->client->trackConversion($visitorCode, $goalId);
    }


    /**
     * @return string[]
     */
    public function getFeatureKeys(): array
    {
        return $this->client->getFeatureList();
    }

    /**
     * This functions parses loaded KameleoonData and takes experiments data
     * @return KameleoonFeatureFlagData[]
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
    public function addPageView(string $visitorCode, string $url, ?string $title, ?array $referrers = null): void
    {
        $this->client->addData($visitorCode, new PageView($url, $title, $referrers));
        $this->client->flush($visitorCode);
    }

    public function getVisitorCodeFromCookies(): ?string
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
     */
    private function getKameleoonFeaturesConfig(): array
    {
        $config = $this->config->getConfig();
        $siteCode = $this->config->getKameleoonSiteCode();
        $workDir = $config->getKameleoonWorkDir();

        $jsonFile = $workDir . KameleoonClientImpl::FILE_CONFIGURATION_NAME . $siteCode . ".json";

        if (!file_exists($jsonFile)) {
            throw new \RuntimeException("Kameleoon config file is not found: {$jsonFile}");
        }

        $jsonContent = file_get_contents($jsonFile);
        $configData = json_decode($jsonContent, true);

        $featureFlags = $configData['featureFlags'] ?? [];

        return array_map(fn(array $item) =>
            new KameleoonFeatureFlagData(
                $item['id'],
                $item['featureKey'],
                $item['environmentEnabled'],
            ), $featureFlags);
    }
}
