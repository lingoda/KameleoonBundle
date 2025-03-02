<?php

declare(strict_types=1);

namespace spec\Lingoda\KameleoonBundle\Kameleoon;

use Kameleoon\Data\CustomData;
use Kameleoon\Data\UserAgent;
use Kameleoon\KameleoonClient;
use Kameleoon\KameleoonClientConfig;
use Kameleoon\Types\Variation;
use Lingoda\KameleoonBundle\DTO\KameleoonUserData;
use Lingoda\KameleoonBundle\DTO\KameleoonUserDataSet;
use Lingoda\KameleoonBundle\Kameleoon\KameleoonConfig;
use Lingoda\KameleoonBundle\Kameleoon\KameleoonFeatureProvider;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\Assert\Assert;

class KameleoonFeatureProviderSpec extends ObjectBehavior
{
    private const VISITOR_CODE = 'test_visitor_code';

    public function let(KameleoonClient $client, RequestStack $requestStack, Request $request, Variation $variation, KameleoonConfig $config, KameleoonClientConfig $clientConfig)
    {

        $variation->isActive()->willReturn(true);
        $variation->key = 'on';
        $cookies = new InputBag();
        $request->cookies = $cookies;
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3';
        $headers = new HeaderBag(['User-Agent' => $userAgent]);
        $request->headers = $headers;
        $requestStack->getCurrentRequest()->willReturn($request);

        $clientConfig->getKameleoonWorkDir()->willReturn('work_dir');
        $config->getConfig()->willReturn($clientConfig);
        $config->getKameleoonSiteCode()->willReturn('site_code');
        $this->beConstructedWith($client, $requestStack, $config);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(KameleoonFeatureProvider::class);
    }

    public function it_checks_if_feature_is_active(
        KameleoonClient $client,
        Variation $variation,
    ) {
        $featureKey = 'my_awesome_test_feature1';
        $client->addData(self::VISITOR_CODE, Argument::type(UserAgent::class))->shouldBeCalled();
        $client->getVariation(self::VISITOR_CODE, $featureKey)->willReturn($variation);
        $this->isFeatureActive(self::VISITOR_CODE, $featureKey)->shouldReturn(true);
    }

    public function it_sends_user_agent_on_feature_evaluation(
        KameleoonClient $client,
        RequestStack $requestStack,
        Request $request,
        Variation $variation,
    ) {
        $featureKey = 'my_awesome_test_feature1';
        $client->getVariation(self::VISITOR_CODE, $featureKey)->willReturn($variation);
        $this->isFeatureActive(self::VISITOR_CODE, $featureKey)->shouldReturn(true);
        $client->addData(self::VISITOR_CODE, Argument::type(UserAgent::class))->shouldHaveBeenCalled();
    }
    
    public function it_sends_custom_data_on_feature_evaluation(
        KameleoonClient $client,
        Variation $variation,
    ) {
        $featureKey = 'my_awesome_test_feature1';
        $dataSet = new KameleoonUserDataSet();
        $data = new KameleoonUserData(3, 'true');
        $data2 = new KameleoonUserData(1, 'German');
        $dataSet->addData($data);
        $dataSet->addData($data2);
        
        $client->getVariation(self::VISITOR_CODE, $featureKey)->willReturn($variation);
        $this->isFeatureActive(self::VISITOR_CODE, $featureKey, $dataSet)->shouldReturn(true);
        $client->addData(self::VISITOR_CODE, Argument::type(UserAgent::class))->shouldHaveBeenCalled();
        $client->addData(self::VISITOR_CODE, Argument::type(CustomData::class))->shouldHaveBeenCalled();
        $client->flush(self::VISITOR_CODE)->shouldHaveBeenCalled();
    }

    public function it_returns_active_features_list(
        KameleoonClient $client,
    ) {
        $featureList = [
            'my_awesome_test_feature1' => new Variation('on', null, null, []),
            'my_awesome_test_feature2' => new Variation('on', null, null, []),
        ];

        $client->getVariations(self::VISITOR_CODE, true, false)->willReturn($featureList);

        Assert::eq(
            $featureList,
            $this->getActiveFeatures(self::VISITOR_CODE)->getWrappedObject()
        );
    }


    public function it_adds_data_for_user(KameleoonClient $client)
    {
        $data = new KameleoonUserData(3, 'true');

        $client->addData(
            self::VISITOR_CODE,
            Argument::that(
                fn(CustomData $d) => $d->getId() === $data->index && $d->getValues()[0] === $data->value
            ),
        )->shouldBeCalledOnce();
        $client->flush(self::VISITOR_CODE)->shouldBeCalledOnce();

        $this->addCustomData(self::VISITOR_CODE, $data);
    }
}
