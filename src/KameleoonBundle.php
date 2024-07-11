<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle;

use Lingoda\KameleoonBundle\Kameleoon\KameleoonConfig;
use Lingoda\KameleoonBundle\Kameleoon\KameleoonEnvironmentMapper;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class KameleoonBundle extends AbstractBundle
{
    protected string $extensionAlias = 'lingoda_kameleoon';

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('client_id')->isRequired()->end()
                ->scalarNode('client_secret')->isRequired()->end()
                ->scalarNode('site_code')->isRequired()->end()
                ->scalarNode('work_dir')->defaultValue('/tmp/app/cache/dev/kameleoon')->end()
                ->scalarNode('environment')->defaultValue('%kernel.environment%')->end()
                ->booleanNode('debug_mode')->defaultValue('%kernel.debug%')->end()
                ->integerNode('refresh_interval_minute')->defaultValue(60)->end()
                ->integerNode('default_timeout_millisecond')->defaultValue(10000)->end()
                ->arrayNode('cookie_options')
                    ->children()
                        ->scalarNode('domain')->isRequired()->end()
                        ->booleanNode('secure')->defaultValue(false)->end()
                        ->booleanNode('http_only')->defaultValue(false)->end()
                        ->scalarNode('same_site')->defaultValue('Lax')->end()
                ->end()
            ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        $container->services()
            ->get(KameleoonConfig::class)
            ->arg(1, $config['client_id'])
            ->arg(2, $config['client_secret'])
            ->arg(3, $config['site_code'])
            ->arg(4, $config['debug_mode'])
            ->arg(5, $config['work_dir'])
            ->arg(6, $config['refresh_interval_minute'])
            ->arg(7, $config['default_timeout_millisecond'])
            ->arg(8, $config['cookie_options'])
        ;

        $container->services()
            ->get(KameleoonEnvironmentMapper::class)
            ->arg(0, $config['environment'])
        ;
    }
}
