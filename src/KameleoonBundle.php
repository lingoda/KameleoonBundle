<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class KameleoonBundle extends AbstractBundle
{
    protected string $extensionAlias = 'lingoda_kameleoon';

    public function configure(DefinitionConfigurator $definition): void
    {
        /* @phpstan-ignore-next-line */
        $definition->rootNode()
            ->children()
                ->scalarNode('client_id')->isRequired()->end()
                ->scalarNode('client_secret')->isRequired()->end()
                ->scalarNode('site_code')->isRequired()->end()
                ->scalarNode('work_dir')->defaultValue('/tmp/app/cache/dev/kameleoon')->end()
                ->scalarNode('environment')->defaultValue('development')->end()
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

    /**
     * @param array<string,mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        foreach ($config as $key => $value) {
            $builder->setParameter(
                sprintf('%s.%s', $this->extensionAlias, $key),
                $value
            );
        }

        $container->import('../config/services.yaml');
    }
}
