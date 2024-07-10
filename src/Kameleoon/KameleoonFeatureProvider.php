<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\Kameleoon;

use Kameleoon\Data\CustomData;
use Kameleoon\KameleoonClient as KameleoonClientInterface;
use Lingoda\KameleoonBundle\DTO\KameleoonUserData;
use Lingoda\KameleoonBundle\DTO\KameleoonUserDataSet;
use Lingoda\KameleoonBundle\User\UserInterface;

class KameleoonFeatureProvider
{
    private KameleoonClientInterface $client;

    public function __construct(KameleoonClient $kameleoonClient)
    {
        $this->client = $kameleoonClient->getClient();
    }

    public function isFeatureActive(UserInterface $user, string $featureKey): bool
    {
        return $this->client->isFeatureActive($this->getVisitorCode($user), $featureKey);
    }

    /**
     * @return string[]
     */
    public function getActiveFeatureListForVisitor(UserInterface $user): array
    {
        return $this->client->getActiveFeatures($this->getVisitorCode($user));
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
