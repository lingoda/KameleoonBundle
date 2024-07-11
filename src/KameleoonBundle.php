<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class KameleoonBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('client_id')->isRequired()->end()
                ->scalarNode('client_secret')->isRequired()->end()
                ->scalarNode('site_code')->isRequired()->end()
                ->scalarNode('work_dir')->defaultValue('/tmp/app/cache/dev/kameleoon')->end()
                ->scalarNode('debug_mode')->defaultValue($this->container->getParameter('kernel.debug'))->end()
                ->integerNode('refresh_interval_minute')->defaultValue(60)->end()
                ->integerNode('default_timeout_millisecond')->defaultValue(10000)->end()
                ->arrayNode('cookie_options')
                    ->children()
                        ->scalarNode('domain')->isRequired()->end()
                        ->booleanNode('secure')->defaultValue(false)->end()
                        ->booleanNode('http_only')->defaultValue(false)->end()
                        ->scalarNode('samesite')->defaultValue('Lax')->end()
                ->end()

        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');
    }
}
