<?php

declare(strict_types=1);

namespace NetIdea\WebBase\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * Provides global Twig variables for company data, site configuration, and social links.
 */
class WebBaseExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @param array<string, string|null> $company
     * @param array<string, string|null> $site
     * @param array<string, string|null> $social
     */
    public function __construct(
        private readonly array $company,
        private readonly array $site,
        private readonly array $social,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getGlobals(): array
    {
        return [
            'company' => $this->company,
            'site' => $this->site,
            'social' => $this->social,
            // Shortcuts for common values
            'brandName' => $this->site['brand_name'] ?? 'Your Brand',
            'siteName' => $this->site['site_name'] ?? 'Your Site',
            'baseUrl' => $this->site['base_url'] ?? '',
            'defaultDescription' => $this->site['default_description'] ?? '',
            'defaultKeywords' => $this->site['default_keywords'] ?? '',
        ];
    }
}
