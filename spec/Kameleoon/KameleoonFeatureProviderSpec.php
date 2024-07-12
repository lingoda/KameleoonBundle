<?php

declare(strict_types=1);

namespace spec\Lingoda\KameleoonBundle\Kameleoon;

use Kameleoon\Data\CustomData;
use Kameleoon\KameleoonClient;
use Lingoda\KameleoonBundle\DTO\KameleoonUserData;
use Lingoda\KameleoonBundle\DTO\KameleoonUserDataSet;
use Lingoda\KameleoonBundle\Enum\KameleoonCustomDataEnum;
use Lingoda\KameleoonBundle\Kameleoon\KameleoonFeatureProvider;
use Lingoda\KameleoonBundle\User\UserInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Webmozart\Assert\Assert;

class KameleoonFeatureProviderSpec extends ObjectBehavior
{
    private const USER_EMAIL = 'user_test1@example.com';
    private const VISITOR_CODE = 'test_visitor_code';

    public function let(KameleoonClient $client, UserInterface $user)
    {
        $user->getEmail()->willReturn(self::USER_EMAIL);
        $client->getVisitorCode(self::USER_EMAIL)->willReturn(self::VISITOR_CODE);

        $this->beConstructedWith($client);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(KameleoonFeatureProvider::class);
    }

    public function it_checks_if_feature_is_active(KameleoonClient $client, UserInterface $user)
    {
        $featureKey = 'my_awesome_test_feature1';
        $client->isFeatureActive(self::VISITOR_CODE, $featureKey)->willReturn(true);

        Assert::true($this->isFeatureActive($user, $featureKey)->getWrappedObject());
    }

    public function it_returns_active_feature_list(KameleoonClient $client, UserInterface $user)
    {
        $featureList = [
            'my_awesome_test_feature1',
            'my_awesome_test_feature2',
        ];
        $client->getActiveFeatures(self::VISITOR_CODE)->willReturn($featureList);

        Assert::eq(
            $featureList,
            $this->getActiveFeatureListForVisitor($user)->getWrappedObject()
        );
    }

    public function it_returns_feature_variation_key(KameleoonClient $client, UserInterface $user)
    {
        $featureKey = 'my_awesome_test_feature1';
        $featureKeyVariation = 'my_awesome_test_feature1_variation1';
        $client->getFeatureVariationKey(self::VISITOR_CODE, $featureKey)->willReturn($featureKeyVariation);

        Assert::eq(
            $featureKeyVariation,
            $this->getFeatureVariationKey($user, $featureKey)->getWrappedObject()
        );
    }

    public function it_returns_full_feature_list(KameleoonClient $client)
    {
        $featureList = [
            'my_awesome_test_feature1',
            'my_awesome_test_feature2',
        ];
        $client->getFeatureList()->willReturn($featureList);
        Assert::eq(
            $featureList,
            $this->getFeatureList()->getWrappedObject()
        );
    }

    public function it_adds_data_for_user(KameleoonClient $client, UserInterface $user)
    {
        $data = new KameleoonUserData(KameleoonCustomDataEnum::IS_LONGOODIE, true);

        $client->addData(
            self::VISITOR_CODE,
            Argument::that(
                fn (CustomData $d) => $d->getId() === 0 && count($d->getValues()) === 1 && $d->getValues()[0] == true
            )
        )->shouldBeCalledOnce();
        $client->flush(self::VISITOR_CODE)->shouldBeCalledOnce();

        $this->addData($user, $data);
    }

    public function it_adds_data_set_for_user(KameleoonClient $client, UserInterface $user)
    {
        $item1 = new KameleoonUserData(KameleoonCustomDataEnum::IS_STUDENT, true);
        $item2 = new KameleoonUserData(KameleoonCustomDataEnum::SECTION, 'German');
        $dataSet = (new KameleoonUserDataSet())
            ->addData($item1)
            ->addData($item2)
        ;

        $client->addData(
            self::VISITOR_CODE,
            Argument::that(
                fn (CustomData $d) => $d->getId() === 9 && count($d->getValues()) === 1 && $d->getValues()[0] == true
            )
        )->shouldBeCalled();
        $client->addData(
            self::VISITOR_CODE,
            Argument::that(
                fn (CustomData $d) => $d->getId() === 1 && count($d->getValues()) === 1 && $d->getValues()[0] == 'German'
            )
        )->shouldBeCalled();

        $client->flush(self::VISITOR_CODE)->shouldBeCalledOnce();

        $this->addDataSet($user, $dataSet);
    }
}
