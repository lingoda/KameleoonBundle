<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\Kameleoon;

use Carbon\CarbonImmutable;
use Kameleoon\Data\CustomData;
use Kameleoon\KameleoonClient as KameleoonClientInterface;
use Lingoda\KameleoonBundle\DTO\KameleoonUserData;
use Lingoda\KameleoonBundle\DTO\KameleoonUserDataSet;
use Lingoda\KameleoonBundle\User\UserInterface;
use Psr\Cache\CacheItemPoolInterface;

class KameleoonFeatureProvider
{
    private const CACHE_TTL_HOURS = 12;

    public function __construct(
        private readonly KameleoonClientInterface $client,
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    public function isFeatureActive(UserInterface $user, string $featureKey): bool
    {
        $visitorCode = $this->getVisitorCode($user);
        $cacheKey = md5($visitorCode) . '_feature_' . $featureKey;
        $cacheItem = $this->cache->getItem($cacheKey);
        if (!$cacheItem->isHit()) {
            $cacheItem->set(
                $this->client->isFeatureActive($visitorCode, $featureKey)
            );
            $cacheItem->expiresAt(CarbonImmutable::now()->addHours(self::CACHE_TTL_HOURS));

            $this->cache->save($cacheItem);
        }

        return $cacheItem->get();
    }

    /**
     * @return string[]
     */
    public function getActiveFeatureListForVisitor(UserInterface $user): array
    {
        $visitorCode = $this->getVisitorCode($user);
        $cacheKey = md5($visitorCode) . '_active_features';
        $cacheItem = $this->cache->getItem($cacheKey);
        if (!$cacheItem->isHit()) {
            $cacheItem->set(
                array_keys($this->client->getActiveFeatures($this->getVisitorCode($user)))
            );
            $cacheItem->expiresAt(CarbonImmutable::now()->addHours(self::CACHE_TTL_HOURS));

            $this->cache->save($cacheItem);
        }

        return $cacheItem->get();
    }

    public function getFeatureVariationKey(UserInterface $user, string $featureKey): string
    {
        return $this->client->getFeatureVariationKey($this->getVisitorCode($user), $featureKey);
    }

    /**
     * @return string[]
     */
    public function getFeatureList(): array
    {
        return $this->client->getFeatureList();
    }

    public function addData(UserInterface $user, KameleoonUserData $data): void
    {
        $this->client->addData($this->getVisitorCode($user), new CustomData($data->id->value, (string)$data->value));
        $this->client->flush($this->getVisitorCode($user));
    }

    public function addDataSet(UserInterface $user, KameleoonUserDataSet $dataSet): void
    {
        foreach ($dataSet->getDataSet() as $data) {
            $this->client->addData(
                $this->getVisitorCode($user),
                new CustomData($data->id->value, (string)$data->value)
            );
        }
        $this->client->flush($this->getVisitorCode($user));
    }

    private function getVisitorCode(UserInterface $user): string
    {
        return $this->client->getVisitorCode($user->getEmail());
    }
}
