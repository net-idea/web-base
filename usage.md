# NetIdea WebBase Bundle - Usage Guide

This comprehensive guide provides detailed instructions for integrating the `NetIdea WebBase Bundle` into your Symfony project. The bundle provides a complete foundation for corporate websites with reusable controllers, services, templates, and frontend assets.

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Controller Usage](#controller-usage)
- [Template Overriding](#template-overriding)
- [Content Management](#content-management)
- [Frontend Assets](#frontend-assets)
- [Services Reference](#services-reference)
- [Twig Variables](#twig-variables)
- [Customization Examples](#customization-examples)
- [Troubleshooting](#troubleshooting)

---

## Installation

### Prerequisites

- PHP 8.2 or higher
- Symfony 6.4, 7.x, or 8.x
- Composer
- Node.js 18+ and Yarn (for frontend assets)

### Step 1: Add the Bundle

**Option A: Local Development (Monorepo)**

If the bundle is in your project under `packages/web-base/`:

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

**Option B: Separate Repository**

```json
// composer.json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/net-idea/web-base"
    }
  ],
  "require": {
    "net-idea/web-base": "^1.0"
  }
}
```

### Step 2: Register the Bundle

```php
// config/bundles.php
<?php

return [
    // ... other bundles
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    // Register WebBase AFTER TwigBundle
    NetIdea\WebBase\NetIdeaWebBaseBundle::class => ['all' => true],
];
```

### Step 3: Configure Routes

Create `config/routes/web_base.yaml`:

```yaml
# Load routes from NetIdeaWebBaseBundle
# These routes have priority: -100 so project routes can override them
net_idea_web_base:
  resource: '@NetIdeaWebBaseBundle/config/routes.yaml'
```

### Step 4: Run Composer

```bash
composer dump-autoload
php bin/console cache:clear
```

---

## Configuration

### Bundle Configuration

Create `config/packages/web_base.yaml`:

```yaml
net_idea_web_base:
  # Company/Business Information
  company:
    name: 'Your Company Name'           # Required: Display name
    legal_name: 'Your Company GmbH'     # Optional: Legal entity name
    tagline: 'Your company slogan'      # Optional: Tagline/slogan
    email: 'info@example.com'           # Contact email
    phone: '+49 123 456789'             # Phone number
    fax: null                           # Fax number
    street: 'Musterstraße 1'            # Street address
    zip: '12345'                        # Postal code
    city: 'Musterstadt'                 # City
    country: 'Deutschland'              # Country
    vat_id: 'DE123456789'               # VAT ID (USt-IdNr.)
    tax_number: '123/456/78901'         # Tax number
    register_court: 'Amtsgericht Köln'  # Register court
    register_number: 'HRB 12345'        # Register number
    ceo: 'Max Mustermann'               # CEO/Managing Director
    responsible_person: 'Max Mustermann' # Responsible person (§55 RStV)

  # Website/Brand Information
  site:
    base_url: 'https://example.com'     # Required: Base URL without trailing slash
    brand_name: 'Your Brand'            # Brand name for display
    site_name: 'Your Site - Tagline'    # Full site name for meta tags
    default_description: 'Your default meta description'
    default_keywords: 'keyword1, keyword2, keyword3'
    locale: 'de_DE'                     # Locale for Open Graph
    language: 'de'                      # HTML lang attribute

  # Social Media Links (null = not displayed)
  social:
    facebook: 'https://facebook.com/yourpage'
    instagram: 'https://instagram.com/yourpage'
    twitter: 'https://twitter.com/yourpage'
    linkedin: 'https://linkedin.com/company/yourpage'
    youtube: null
    github: null

  # Mail Configuration
  mail:
    from_address: '%env(string:MAIL_FROM_ADDRESS)%'
    from_name: '%env(default::string:MAIL_FROM_NAME)%'
    to_address: '%env(string:MAIL_TO_ADDRESS)%'
    to_name: '%env(string:MAIL_TO_NAME)%'

  # Content Configuration
  content:
    pages_file: '%kernel.project_dir%/content/_pages.php'
    content_dir: '%kernel.project_dir%/content'
```

### Environment Variables

Add to your `.env`:

```bash
# Mail Configuration
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="Your Company"
MAIL_TO_ADDRESS=contact@example.com
MAIL_TO_NAME="Contact Team"
```

---

## Controller Usage

### Understanding Controller Priority

Bundle controllers have `priority: -100` on their routes, allowing your project controllers to override them with higher priority (default: 0).

### Option 1: Use Bundle Controllers Directly

If you don't need customization, the bundle controllers work out of the box:

- `GET /` → `MainController::main()` → Renders `index` page
- `GET /{slug}` → `MainController::page()` → Renders page by slug
- `GET /kontakt` → `ContactController::contact()` → Renders contact form
- `POST /api/contact` → `ContactController::contactApi()` → Handles form submission

### Option 2: Extend Bundle Controllers

Create your own controllers that extend the bundle controllers:

```php
<?php
// src/Controller/MainController.php

declare(strict_types=1);

namespace App\Controller;

use NetIdea\WebBase\Controller\MainController as BaseMainController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends BaseMainController
{
    // Override the index route with higher priority
    #[Route(path: '/', name: 'app_index', methods: ['GET'])]
    public function main(): Response
    {
        // Add custom logic before calling parent
        return parent::main();
    }

    // Override page handling
    public function page(string $slug = 'index'): Response
    {
        // Custom pre-processing
        if ($slug === 'special-page') {
            return $this->render('pages/special.html.twig', [
                'customData' => $this->getSpecialData(),
            ]);
        }

        return parent::page($slug);
    }

    private function getSpecialData(): array
    {
        return ['key' => 'value'];
    }
}
```

### Option 3: Create Completely New Controllers

For routes that need completely different behavior:

```php
<?php
// src/Controller/ProductController.php

declare(strict_types=1);

namespace App\Controller;

use NetIdea\WebBase\Controller\AbstractBaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/products')]
class ProductController extends AbstractBaseController
{
    #[Route('/', name: 'app_products')]
    public function index(): Response
    {
        return $this->render('products/index.html.twig', [
            'pageMeta' => $this->loadPageMetadata('products'),
        ]);
    }
}
```

---

## Template Overriding

### Template Namespace

Bundle templates are available under the `@NetIdeaWebBase` namespace:

- `@NetIdeaWebBase/base.html.twig`
- `@NetIdeaWebBase/pages/kontakt.html.twig`
- `@NetIdeaWebBase/email/contact_owner.html.twig`
- `@NetIdeaWebBase/_partials/navbar.html.twig`

### Override Strategy

**Priority Order (highest to lowest):**

1. `templates/` (Your project templates)
2. `templates/bundles/NetIdeaWebBaseBundle/` (Bundle template overrides)
3. `@NetIdeaWebBase/` (Bundle default templates)

### Override a Bundle Template

To override the base template:

```twig
{# templates/bundles/NetIdeaWebBaseBundle/base.html.twig #}
{% extends '@NetIdeaWebBase/base.html.twig' %}

{% block masthead %}
  {# Your custom masthead #}
  <header class="my-custom-masthead">
    <h1>{{ company.name }}</h1>
    <p>{{ company.tagline }}</p>
  </header>
{% endblock %}
```

### Extend and Customize

```twig
{# templates/base.html.twig #}
{% extends '@NetIdeaWebBase/base.html.twig' %}

{# Override SEO defaults from configuration #}
{% set brandName = site.brand_name %}
{% set siteName = site.site_name %}
{% set baseUrl = site.base_url %}

{% block stylesheets %}
  {{ parent() }}
  {# Add project-specific styles #}
  {{ encore_entry_link_tags('custom') }}
{% endblock %}
```

### Template Blocks Reference

| Block | Description |
|-------|-------------|
| `title` | Page title (inside `<title>` tag) |
| `stylesheets` | CSS includes |
| `javascripts` | JavaScript includes |
| `masthead` | Header/hero section |
| `body` | Main content wrapper |
| `content` | Inner content area |

---

## Content Management

### Page Configuration File

Create `content/_pages.php`:

```php
<?php
// content/_pages.php

return [
    'start' => [
        'title' => 'Home - Your Company',
        'description' => 'Welcome to Your Company - Your tagline here',
        'keywords' => 'company, service, product',
        'canonical' => '/',
        'robots' => 'index,follow',
        'og_image' => '/assets/og/home.jpg',
        'nav' => true,
        'nav_label' => 'Home',
        'nav_order' => 10,
    ],
    'about' => [
        'title' => 'About Us - Your Company',
        'description' => 'Learn more about our company',
        'canonical' => '/about',
        'robots' => 'index,follow',
        'nav' => true,
        'nav_label' => 'About',
        'nav_order' => 20,
    ],
    'kontakt' => [
        'title' => 'Contact - Your Company',
        'description' => 'Get in touch with us',
        'canonical' => '/kontakt',
        'nav' => true,
        'nav_label' => 'Contact',
        'nav_order' => 100,
    ],
    'datenschutz' => [
        'title' => 'Privacy Policy - Your Company',
        'canonical' => '/datenschutz',
        'robots' => 'noindex,follow',
        'nav' => false,
    ],
    'impressum' => [
        'title' => 'Imprint - Your Company',
        'canonical' => '/impressum',
        'robots' => 'noindex,follow',
        'nav' => false,
    ],
];
```

### Content Files

**Twig Templates** (Priority 1):
Place in `templates/pages/{slug}.html.twig`

```twig
{# templates/pages/about.html.twig #}
{% extends 'base.html.twig' %}

{% block content %}
  <h1>About Us</h1>
  <p>Our story...</p>
{% endblock %}
```

**Markdown Files** (Priority 2):
Place in `content/{slug}.md`

```markdown
# About Us

Our company was founded in 2020...

## Our Mission

We strive to...
```

---

## Frontend Assets

### Webpack Configuration

Add the alias in `webpack.config.js`:

```javascript
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

Encore
  // ... other config
  .addAliases({
    '@web-base': path.resolve(__dirname, 'packages/web-base/frontend'),
  })
```

### Import Bundle Assets

In your `assets/app.ts`:

```typescript
// Import base styles from bundle
import '@web-base/styles/app.scss';

// Import specific scripts from bundle
import '@web-base/scripts/navbar-shrink.ts';
import '@web-base/scripts/theme-toggle.ts';
import '@web-base/scripts/contact-form.ts';

// Your project-specific imports
import './styles/custom.scss';
```

### Override Bundle Styles

```scss
// assets/styles/custom.scss

// Override bundle variables before importing
$primary: #your-color;
$font-family-base: 'Your Font', sans-serif;

// Import bundle base (which uses Bootstrap)
@import '@web-base/styles/app';

// Add your customizations
.my-custom-class {
  color: $primary;
}
```

### Available Frontend Scripts

| Script | Description |
|--------|-------------|
| `navbar-shrink.ts` | Shrinks navbar on scroll |
| `theme-toggle.ts` | Dark/light mode toggle |
| `contact-form.ts` | AJAX contact form handling |
| `contacts.ts` | Contact information utilities |

---

## Services Reference

### NavigationService

Builds navigation from `content/_pages.php`:

```php
use NetIdea\WebBase\Service\NavigationService;

class MyController extends AbstractController
{
    public function __construct(
        private readonly NavigationService $navigation,
    ) {}

    public function index(): Response
    {
        return $this->render('index.html.twig', [
            'navItems' => $this->navigation->getItems(),
        ]);
    }
}
```

### MailManService

Handles email sending:

```php
use NetIdea\WebBase\Service\MailManService;
use NetIdea\WebBase\Entity\FormContactEntity;

class MyService
{
    public function __construct(
        private readonly MailManService $mailMan,
    ) {}

    public function sendContact(FormContactEntity $contact): void
    {
        $this->mailMan->sendContactForm($contact);
    }
}
```

### FormContactService

Handles contact form creation and processing:

```php
use NetIdea\WebBase\Service\FormContactService;

class ContactController
{
    public function __construct(
        private readonly FormContactService $formService,
    ) {}

    public function show(): Response
    {
        $form = $this->formService->getForm();
        return $this->render('contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function submit(): Response
    {
        return $this->formService->handleAjax();
    }
}
```

---

## Twig Variables

The bundle provides global Twig variables:

### Company Data

```twig
{{ company.name }}
{{ company.email }}
{{ company.phone }}
{{ company.street }}
{{ company.zip }} {{ company.city }}
{{ company.country }}
{{ company.vat_id }}
{{ company.ceo }}
```

### Site Data

```twig
{{ site.base_url }}
{{ site.brand_name }}
{{ site.site_name }}
{{ site.default_description }}
{{ site.locale }}
{{ site.language }}

{# Shortcuts #}
{{ brandName }}
{{ siteName }}
{{ baseUrl }}
{{ defaultDescription }}
{{ defaultKeywords }}
```

### Social Links

```twig
{% if social.facebook %}
  <a href="{{ social.facebook }}">Facebook</a>
{% endif %}
{% if social.instagram %}
  <a href="{{ social.instagram }}">Instagram</a>
{% endif %}
```

### Usage Example: Imprint Page

```twig
{# templates/pages/impressum.html.twig #}
{% extends 'base.html.twig' %}

{% block content %}
<h1>Impressum</h1>

<h2>Angaben gemäß § 5 TMG</h2>
<p>
  {{ company.legal_name|default(company.name) }}<br>
  {{ company.street }}<br>
  {{ company.zip }} {{ company.city }}<br>
  {{ company.country }}
</p>

<h2>Kontakt</h2>
<p>
  {% if company.phone %}Telefon: {{ company.phone }}<br>{% endif %}
  {% if company.fax %}Fax: {{ company.fax }}<br>{% endif %}
  E-Mail: {{ company.email }}
</p>

{% if company.vat_id %}
<h2>Umsatzsteuer-ID</h2>
<p>{{ company.vat_id }}</p>
{% endif %}

{% if company.register_court and company.register_number %}
<h2>Registereintrag</h2>
<p>
  Registergericht: {{ company.register_court }}<br>
  Registernummer: {{ company.register_number }}
</p>
{% endif %}

{% if company.ceo %}
<h2>Geschäftsführer</h2>
<p>{{ company.ceo }}</p>
{% endif %}

{% endblock %}
```

---

## Customization Examples

### Example 1: Custom Contact Form

```php
<?php
// src/Controller/ContactController.php

namespace App\Controller;

use NetIdea\WebBase\Controller\ContactController as BaseContactController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends BaseContactController
{
    #[Route(path: '/contact', name: 'app_contact', methods: ['GET'])]
    public function contact(): Response
    {
        // Use English route instead of German
        return parent::contact();
    }
}
```

### Example 2: Multi-Language Support

```php
<?php
// src/Controller/MainController.php

namespace App\Controller;

use NetIdea\WebBase\Controller\MainController as BaseMainController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends BaseMainController
{
    #[Route(path: '/{_locale}/', name: 'app_index_localized', 
            requirements: ['_locale' => 'de|en'], methods: ['GET'])]
    public function localizedIndex(string $_locale): Response
    {
        // Handle localized homepage
        return $this->page('index');
    }
}
```

### Example 3: Custom Navigation

```php
<?php
// src/Service/CustomNavigationService.php

namespace App\Service;

use NetIdea\WebBase\Service\NavigationService;

class CustomNavigationService extends NavigationService
{
    public function getItems(): array
    {
        $items = parent::getItems();
        
        // Add a custom item
        $items[] = [
            'slug' => 'external',
            'label' => 'Shop',
            'url' => 'https://shop.example.com',
            'order' => 50,
        ];
        
        // Re-sort
        usort($items, fn($a, $b) => $a['order'] <=> $b['order']);
        
        return $items;
    }
}
```

Register in `services.yaml`:

```yaml
services:
  NetIdea\WebBase\Service\NavigationService:
    class: App\Service\CustomNavigationService
```

---

## Troubleshooting

### Routes Not Found

1. Ensure bundle is registered in `config/bundles.php`
2. Check `config/routes/web_base.yaml` exists
3. Clear cache: `php bin/console cache:clear`
4. Debug routes: `php bin/console debug:router | grep web_base`

### Templates Not Loading

1. Check Twig paths: `php bin/console debug:twig`
2. Ensure `@NetIdeaWebBase` namespace is registered
3. Verify template file locations

### Services Not Found

1. Run `composer dump-autoload`
2. Check service container: `php bin/console debug:container NetIdea`
3. Verify `config/services.yaml` doesn't exclude the bundle namespace

### Configuration Not Applied

1. Check YAML syntax in `config/packages/web_base.yaml`
2. Validate config: `php bin/console debug:config net_idea_web_base`
3. Clear cache after config changes

### Frontend Assets Not Building

1. Verify webpack alias in `webpack.config.js`
2. Check path exists: `packages/web-base/frontend/`
3. Run `yarn install` to ensure dependencies
4. Check for TypeScript errors: `yarn tsc:check`

---

## For AI Agents

When working with this bundle:

1. **Never modify files in `packages/web-base/`** unless explicitly asked
2. **Override, don't duplicate** - extend bundle classes instead of copying code
3. **Use configuration** for company data, never hardcode
4. **Check route priorities** - bundle routes are -100, project routes are 0
5. **Template hierarchy** - project templates override bundle templates
6. **Use `@web-base` alias** for frontend imports
7. **Validate changes** with `php bin/console cache:clear` and `debug:router`
