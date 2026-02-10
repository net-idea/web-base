<?php

declare(strict_types=1);

namespace NetIdea\WebBase;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * NetIdeaWebBaseBundle - A reusable Symfony bundle for website fundamentals.
 *
 * This bundle provides:
 * - Base controllers for page and content handling
 * - Contact form with email notifications
 * - Navigation service based on content/_pages.php
 * - Configurable company data via bundle configuration
 * - Overridable templates and content
 */
class NetIdeaWebBaseBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        // @phpstan-ignore-next-line
        $definition->rootNode()
            ->children()
                // Company information
                ->arrayNode('company')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('name')->defaultValue('Your Company')->end()
                        ->scalarNode('legal_name')->defaultNull()->end()
                        ->scalarNode('tagline')->defaultNull()->end()
                        ->scalarNode('email')->defaultNull()->end()
                        ->scalarNode('phone')->defaultNull()->end()
                        ->scalarNode('fax')->defaultNull()->end()
                        ->scalarNode('street')->defaultNull()->end()
                        ->scalarNode('zip')->defaultNull()->end()
                        ->scalarNode('city')->defaultNull()->end()
                        ->scalarNode('country')->defaultValue('Deutschland')->end()
                        ->scalarNode('vat_id')->defaultNull()->end()
                        ->scalarNode('tax_number')->defaultNull()->end()
                        ->scalarNode('register_court')->defaultNull()->end()
                        ->scalarNode('register_number')->defaultNull()->end()
                        ->scalarNode('ceo')->defaultNull()->end()
                        ->scalarNode('responsible_person')->defaultNull()->end()
                    ->end()
                ->end()
                // Website/Brand information
                ->arrayNode('site')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('base_url')->defaultValue('https://example.com')->end()
                        ->scalarNode('brand_name')->defaultValue('Your Brand')->end()
                        ->scalarNode('site_name')->defaultValue('Your Site Name')->end()
                        ->scalarNode('default_description')->defaultValue('Your default site description')->end()
                        ->scalarNode('default_keywords')->defaultValue('your, keywords, here')->end()
                        ->scalarNode('locale')->defaultValue('de_DE')->end()
                        ->scalarNode('language')->defaultValue('de')->end()
                    ->end()
                ->end()
                // Social media links
                ->arrayNode('social')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('facebook')->defaultNull()->end()
                        ->scalarNode('instagram')->defaultNull()->end()
                        ->scalarNode('twitter')->defaultNull()->end()
                        ->scalarNode('linkedin')->defaultNull()->end()
                        ->scalarNode('youtube')->defaultNull()->end()
                        ->scalarNode('github')->defaultNull()->end()
                    ->end()
                ->end()
                // Mail configuration
                ->arrayNode('mail')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('from_address')->defaultValue('%env(string:MAIL_FROM_ADDRESS)%')->end()
                        ->scalarNode('from_name')->defaultValue('%env(default::string:MAIL_FROM_NAME)%')->end()
                        ->scalarNode('to_address')->defaultValue('%env(string:MAIL_TO_ADDRESS)%')->end()
                        ->scalarNode('to_name')->defaultValue('%env(string:MAIL_TO_NAME)%')->end()
                    ->end()
                ->end()
                // Content directories
                ->arrayNode('content')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('pages_file')->defaultValue('%kernel.project_dir%/content/_pages.php')->end()
                        ->scalarNode('content_dir')->defaultValue('%kernel.project_dir%/content')->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Load bundle services
        $container->import('../config/services.yaml');

        // Set parameters from configuration
        $container->parameters()
            // Company parameters
            ->set('web_base.company.name', $config['company']['name'])
            ->set('web_base.company.legal_name', $config['company']['legal_name'])
            ->set('web_base.company.tagline', $config['company']['tagline'])
            ->set('web_base.company.email', $config['company']['email'])
            ->set('web_base.company.phone', $config['company']['phone'])
            ->set('web_base.company.fax', $config['company']['fax'])
            ->set('web_base.company.street', $config['company']['street'])
            ->set('web_base.company.zip', $config['company']['zip'])
            ->set('web_base.company.city', $config['company']['city'])
            ->set('web_base.company.country', $config['company']['country'])
            ->set('web_base.company.vat_id', $config['company']['vat_id'])
            ->set('web_base.company.tax_number', $config['company']['tax_number'])
            ->set('web_base.company.register_court', $config['company']['register_court'])
            ->set('web_base.company.register_number', $config['company']['register_number'])
            ->set('web_base.company.ceo', $config['company']['ceo'])
            ->set('web_base.company.responsible_person', $config['company']['responsible_person'])
            // Site parameters
            ->set('web_base.site.base_url', $config['site']['base_url'])
            ->set('web_base.site.brand_name', $config['site']['brand_name'])
            ->set('web_base.site.site_name', $config['site']['site_name'])
            ->set('web_base.site.default_description', $config['site']['default_description'])
            ->set('web_base.site.default_keywords', $config['site']['default_keywords'])
            ->set('web_base.site.locale', $config['site']['locale'])
            ->set('web_base.site.language', $config['site']['language'])
            // Social parameters
            ->set('web_base.social.facebook', $config['social']['facebook'])
            ->set('web_base.social.instagram', $config['social']['instagram'])
            ->set('web_base.social.twitter', $config['social']['twitter'])
            ->set('web_base.social.linkedin', $config['social']['linkedin'])
            ->set('web_base.social.youtube', $config['social']['youtube'])
            ->set('web_base.social.github', $config['social']['github'])
            // Mail parameters
            ->set('web_base.mail.from_address', $config['mail']['from_address'])
            ->set('web_base.mail.from_name', $config['mail']['from_name'])
            ->set('web_base.mail.to_address', $config['mail']['to_address'])
            ->set('web_base.mail.to_name', $config['mail']['to_name'])
            // Content parameters
            ->set('web_base.content.pages_file', $config['content']['pages_file'])
            ->set('web_base.content.content_dir', $config['content']['content_dir']);
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Configure Twig to use bundle templates
        $builder->prependExtensionConfig('twig', [
            'paths' => [
                '%kernel.project_dir%/packages/web-base/backend/templates' => 'NetIdeaWebBase',
            ],
        ]);
    }
}
