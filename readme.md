# NetIdea WebBase Bundle

A reusable Symfony Bundle for corporate websites. Provides a complete foundation including controllers, services, entities, templates, and frontend assets.

## Overview

This bundle provides:

- **Backend**: Complete Symfony bundle with controllers, services, entities, forms, and Twig templates
- **Frontend**: TypeScript scripts and SCSS styles for Bootstrap 5-based UIs
- **Configuration**: Centralized company data, site settings, and mail configuration

## Features

- 🎯 **Ready-to-use Controllers** - Page rendering with Twig and Markdown support
- 📧 **Contact Form** - Complete form with validation, rate limiting, and email notifications
- 🎨 **Theming** - Dark/light mode support with Bootstrap 5
- 🔧 **Fully Configurable** - Company data, site settings via YAML configuration
- 🔄 **Overridable** - All controllers, templates, and services can be customized
- 📱 **Responsive** - Mobile-first Bootstrap 5 design

## Quick Start

### 1. Register the Bundle

```php
// config/bundles.php
return [
    // ...
    NetIdea\WebBase\NetIdeaWebBaseBundle::class => ['all' => true],
];
```

### 2. Add Autoloading (for local development)

```json
// composer.json
{
  "autoload": {
    "psr-4": {
      "App\\": "src/",
      "NetIdea\\WebBase\\": "packages/web-base/backend/src/"
    }
  }
}
```

### 3. Configure the Bundle

```yaml
# config/packages/web_base.yaml
net_idea_web_base:
  company:
    name: 'Your Company'
    email: 'info@example.com'
  site:
    base_url: 'https://example.com'
    brand_name: 'Your Brand'
```

### 4. Add Routes

```yaml
# config/routes/web_base.yaml
net_idea_web_base:
  resource: '@NetIdeaWebBaseBundle/config/routes.yaml'
```

### 5. Configure Webpack for Frontend Assets

```javascript
// webpack.config.js
.addAliases({
  '@web-base': path.resolve(__dirname, 'packages/web-base/frontend'),
})
```

## Structure

```
backend/
├── config/                # Bundle configuration
│   ├── routes.yaml       # Route definitions
│   └── services.yaml     # Service definitions
├── src/                   # PHP source code
│   ├── Controller/       # HTTP controllers
│   ├── Service/          # Business logic services
│   ├── Entity/           # Doctrine entities
│   ├── Form/             # Form types
│   ├── Repository/       # Doctrine repositories
│   ├── Twig/             # Twig extensions
│   └── NetIdeaWebBaseBundle.php
├── templates/            # Twig templates
│   ├── _partials/        # Reusable template partials
│   ├── email/            # Email templates
│   └── pages/            # Page templates
├── content/              # Default content files
└── tests/                # PHPUnit tests

frontend/
├── scripts/              # TypeScript modules
│   ├── contact-form.ts   # Contact form handling
│   ├── navbar-shrink.ts  # Navbar scroll behavior
│   └── theme-toggle.ts   # Light/dark theme switcher
├── styles/               # SCSS stylesheets
│   ├── _variables.scss   # SCSS variables
│   ├── _theme.scss       # Theme styles
│   └── app.scss          # Main application styles
├── app.ts                # Main entry point
└── package.json          # Frontend dependencies
```

## Documentation

- **[Usage Guide](./usage.md)** - Detailed integration instructions
- **[Features Overview](./features.md)** - Complete feature documentation

## Requirements

- PHP >= 8.2
- Symfony 6.4 / 7.x / 8.x
- Node.js >= 18 (for frontend)

## License

MIT
