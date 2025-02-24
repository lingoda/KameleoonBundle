<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\Kameleoon;

use Kameleoon\Data\CustomData;
use Kameleoon\Data\PageView;
use Kameleoon\Data\UserAgent;
use Kameleoon\KameleoonClient as KameleoonClientInterface;
use Kameleoon\Types\Variation;
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
        private readonly RequestStack $requestStack
    ) {
    }

    /**
     * visitorCode should be a uniq string that would be identified with a user before AND after creation
     */
    public function getFeatureVariationValue(string $visitorCode, string $featureKey, ?KameleoonUserDataSet $customDataset = null): KameleoonVariationKeyEnum
    {
        // @TODO should I throw ValueError here? or a custom error describing what to do on Kameleoon side?
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

        // should I add throwable ValueError from here?
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
     * @param ?KameleoonUserDataSet $customDataset
     */
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
}
