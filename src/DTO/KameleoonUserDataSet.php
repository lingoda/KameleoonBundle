<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\DTO;

class KameleoonUserDataSet
{
    /** @var KameleoonUserData[] */
    private array $dataSet = [];

    public function addData(KameleoonUserData $data): self
    {
        $this->dataSet[] = $data;
        return $this;
    }

    /**
     * @return KameleoonUserData[]
     */
    public function getDataSet(): array
    {
        return $this->dataSet;
    }
}
