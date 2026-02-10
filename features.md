# NetIdea WebBase Bundle - Features Overview

This document provides a comprehensive overview of all features included in the WebBase Bundle. The bundle is designed to provide a complete, reusable foundation for corporate Symfony websites.

## 🎯 Core Concept

The WebBase Bundle follows these principles:

- **Convention over Configuration** - Works out of the box with sensible defaults
- **Override Everything** - All components can be customized or replaced
- **Separation of Concerns** - Clear distinction between bundle (base) and project (customizations)
- **Configuration-Driven** - Company data, site settings, and mail configuration via YAML

---

## 📦 Bundle Architecture

```
packages/web-base/
├── backend/
│   ├── config/
│   │   ├── routes.yaml          # Bundle route definitions
│   │   └── services.yaml        # Bundle service definitions
│   ├── content/
│   │   ├── _pages.php           # Default page metadata
│   │   └── *.md                 # Default content files
│   ├── src/
│   │   ├── Command/             # CLI commands
│   │   ├── Controller/          # HTTP controllers
│   │   ├── Entity/              # Form/data entities
│   │   ├── Form/                # Form types
│   │   ├── Repository/          # Data repositories
│   │   ├── Service/             # Business logic services
│   │   ├── Twig/                # Twig extensions
│   │   └── NetIdeaWebBaseBundle.php
│   ├── templates/
│   │   ├── _partials/           # Reusable template parts
│   │   ├── email/               # Email templates
│   │   ├── pages/               # Page templates
│   │   └── base.html.twig       # Base layout
│   └── tests/                   # Bundle tests
├── frontend/
│   ├── scripts/                 # TypeScript modules
│   ├── styles/                  # SCSS stylesheets
│   ├── app.ts                   # Main entry point
│   └── package.json             # Frontend dependencies
├── composer.json                # PHP package definition
├── features.md                  # This file
├── readme.md                    # Quick start guide
└── usage.md                     # Detailed usage guide
```

---

## 🔧 Backend Features

### 1. Bundle Configuration System

The bundle uses Symfony's AbstractBundle with full configuration support:

```yaml
# config/packages/web_base.yaml
net_idea_web_base:
  company:
    name: 'Company Name'
    email: 'info@example.com'
    # ... 15+ company fields
  site:
    base_url: 'https://example.com'
    brand_name: 'Brand'
    # ... 7 site configuration fields
  social:
    facebook: 'https://...'
    # ... 6 social media fields
  mail:
    from_address: '%env(...)%'
    # ... 4 mail configuration fields
  content:
    pages_file: '%kernel.project_dir%/content/_pages.php'
    content_dir: '%kernel.project_dir%/content'
```

**Company Configuration Fields:**

| Field | Description | Example |
|-------|-------------|---------|
| `name` | Display name | "UniSurf" |
| `legal_name` | Legal entity name | "UniSurf GmbH" |
| `tagline` | Company slogan | "Unique Surfing" |
| `email` | Contact email | "info@unisurf.de" |
| `phone` | Phone number | "+49 123 456789" |
| `fax` | Fax number | null |
| `street` | Street address | "Musterstraße 1" |
| `zip` | Postal code | "41542" |
| `city` | City | "Dormagen" |
| `country` | Country | "Deutschland" |
| `vat_id` | VAT ID (USt-IdNr.) | "DE123456789" |
| `tax_number` | Tax number | "123/456/78901" |
| `register_court` | Register court | "Amtsgericht Neuss" |
| `register_number` | Register number | "HRB 12345" |
| `ceo` | CEO name | "Max Mustermann" |
| `responsible_person` | §55 RStV responsible | "Max Mustermann" |

---

### 2. Controllers

#### AbstractBaseController

Base controller with page metadata loading:

```php
abstract class AbstractBaseController extends AbstractController
{
    public function loadPageMetadata(string $slug): array
    {
        // Loads from content/_pages.php
        // Returns: title, description, canonical, robots, og_image, etc.
    }
}
```

**Features:**
- Automatic page metadata loading
- Fallback defaults for missing pages
- Configurable content directory

#### MainController

Handles all page rendering:

| Route | Name | Description |
|-------|------|-------------|
| `GET /` | `web_base_index` | Homepage (renders `index` page) |
| `GET /{slug}` | `web_base_page` | Dynamic page rendering |

**Page Resolution Order:**
1. Twig template: `templates/pages/{slug}.html.twig`
2. Markdown file: `content/{slug}.md`
3. 404 Not Found

**Route Priority:** `-100` (can be overridden by project routes with priority 0)

#### ContactController

Handles contact form:

| Route | Name | Description |
|-------|------|-------------|
| `GET /kontakt` | `web_base_contact` | Contact form page |
| `POST /api/contact` | `web_base_api_contact` | AJAX form submission |

---

### 3. Services

#### NavigationService

Builds navigation menu from `content/_pages.php`:

```php
readonly class NavigationService
{
    public function getItems(): array
    {
        // Returns array of navigation items:
        // [
        //     ['slug' => 'about', 'label' => 'About', 'url' => '/about', 'order' => 20],
        //     ...
        // ]
    }
}
```

**Page Metadata for Navigation:**

```php
// content/_pages.php
return [
    'about' => [
        'nav' => true,           // Show in navigation
        'nav_label' => 'About',  // Navigation label
        'nav_order' => 20,       // Sort order
    ],
];
```

#### MailManService

Complete email handling service:

```php
class MailManService
{
    public function sendContactForm(FormContactEntity $contact): void;
    public function sendBookingVisitorConfirmationRequest(FormBookingEntity $booking, string $confirmUrl): void;
    public function sendBookingOwnerNotification(FormBookingEntity $booking): void;
    public function sendBookingVisitorConfirmation(FormBookingEntity $booking): void;
}
```

**Features:**
- Dual email sending (owner + visitor copy)
- Theme-aware HTML emails (light/dark)
- Reply-to header with visitor email
- Fallback addresses for test environments
- Full logging of send operations

#### FormContactService

Contact form creation and processing:

```php
class FormContactService extends AbstractFormService
{
    public function getForm(): FormInterface;
    public function handleAjax(): Response;
}
```

**Features:**
- Symfony Form integration
- AJAX handling with JSON responses
- Rate limiting (configurable)
- Honeypot spam protection
- Automatic email sending

#### AbstractFormService

Base class for form services with security features:

**Rate Limiting:**
- Configurable time window (default: 1 hour)
- Minimum interval between submissions (default: 30 seconds)
- Maximum submissions per window (default: 10)
- Session-based tracking

**Spam Protection:**
- Honeypot field detection
- Hidden field validation

---

### 4. Entities

#### FormContactEntity

Contact form data structure:

| Field | Type | Validation |
|-------|------|------------|
| `name` | string | Required, 2-100 chars |
| `emailAddress` | string | Required, valid email |
| `subject` | string | Required, 3-200 chars |
| `message` | string | Required, 10-5000 chars |
| `phone` | string | Optional |
| `copy` | bool | Send copy to visitor |
| `privacy` | bool | Required true |
| `createdAt` | DateTime | Auto-set |

#### FormBookingEntity

Booking/appointment form data structure (if using booking feature).

---

### 5. Twig Extension

The `WebBaseExtension` provides global variables:

```twig
{# Company data #}
{{ company.name }}
{{ company.email }}
{{ company.phone }}
{{ company.street }}
{{ company.zip }}
{{ company.city }}

{# Site data #}
{{ site.base_url }}
{{ site.brand_name }}
{{ site.site_name }}

{# Shortcuts #}
{{ brandName }}
{{ siteName }}
{{ baseUrl }}
{{ defaultDescription }}
{{ defaultKeywords }}

{# Social links #}
{{ social.facebook }}
{{ social.instagram }}
{{ social.twitter }}
{{ social.linkedin }}
```

---

### 6. Templates

#### Base Layout (`base.html.twig`)

Full HTML5 document with:
- SEO meta tags (title, description, keywords, robots)
- Open Graph tags
- Twitter Card tags
- Canonical URL
- Favicon support
- Webpack Encore integration

**Blocks:**

| Block | Description |
|-------|-------------|
| `title` | Page title |
| `stylesheets` | CSS includes |
| `masthead` | Header/hero section |
| `body` | Main content wrapper |
| `content` | Page content |
| `javascripts` | JS includes |

#### Partials (`_partials/`)

| File | Description |
|------|-------------|
| `navbar.html.twig` | Navigation bar |
| `footer.html.twig` | Site footer |

#### Email Templates (`email/`)

| File | Description |
|------|-------------|
| `contact_owner.html.twig` | HTML email to site owner |
| `contact_owner.txt.twig` | Plain text email to owner |
| `contact_visitor.html.twig` | HTML copy to visitor |
| `contact_visitor.txt.twig` | Plain text copy to visitor |

---

## 🎨 Frontend Features

### 1. Styles (SCSS)

#### File Structure

| File | Description |
|------|-------------|
| `app.scss` | Main entry point, imports all partials |
| `_variables.scss` | Bootstrap variable overrides |
| `_base.scss` | Base element styles |
| `_theme.scss` | Theme utilities |
| `_theme-light.scss` | Light theme colors |
| `_theme-dark.scss` | Dark theme colors |
| `_forms.scss` | Form styling |
| `_forms-light.scss` | Light theme form styles |
| `_forms-dark.scss` | Dark theme form styles |
| `fonts.scss` | Web font imports |

#### Features

- **Bootstrap 5 Integration** - Full Bootstrap with custom variables
- **Dark/Light Theme Support** - Complete theming system
- **Custom Font Loading** - Self-hosted web fonts (WOFF2)
- **Responsive Design** - Mobile-first approach

### 2. Scripts (TypeScript)

#### Available Modules

| Module | Description |
|--------|-------------|
| `navbar-shrink.ts` | Shrinks navbar on scroll |
| `theme-toggle.ts` | Dark/light mode toggle with persistence |
| `contact-form.ts` | AJAX contact form with validation |
| `contacts.ts` | Contact information utilities |

#### Theme Toggle Features

- Detects system preference (`prefers-color-scheme`)
- Persists choice in localStorage
- Smooth transitions
- Accessible toggle button

#### Contact Form Features

- Client-side validation
- AJAX submission
- Loading states
- Success/error messages
- Rate limit handling

---

## 🔄 Override Mechanisms

### 1. Controller Overriding

Bundle controllers have `priority: -100`. Create your own with higher priority:

```php
// Your controller (priority: 0, wins over bundle)
#[Route(path: '/', name: 'app_index')]
public function index(): Response { ... }
```

### 2. Template Overriding

**Priority order:**

1. `templates/pages/about.html.twig` (project)
2. `templates/bundles/NetIdeaWebBaseBundle/pages/about.html.twig` (override)
3. `@NetIdeaWebBase/pages/about.html.twig` (bundle)

### 3. Service Overriding

Replace bundle services in your `services.yaml`:

```yaml
services:
  NetIdea\WebBase\Service\NavigationService:
    class: App\Service\CustomNavigationService
```

### 4. Content Overriding

- Project's `content/_pages.php` takes precedence
- Project's `content/*.md` files are used
- Bundle provides fallback content

### 5. Style Overriding

Import bundle styles and override variables:

```scss
// Override before import
$primary: #your-color;

// Import bundle styles
@import '@web-base/styles/app';

// Add your customizations
.custom-class { ... }
```

---

## 📋 Requirements

### Backend

- PHP >= 8.2
- Symfony 6.4 / 7.x / 8.x
- Symfony TwigBundle
- Symfony FrameworkBundle
- Symfony Mailer (for email features)
- Doctrine ORM (for entity features)
- erusev/parsedown (for Markdown rendering)

### Frontend

- Node.js >= 18
- Yarn or npm
- Webpack Encore
- Bootstrap 5
- TypeScript

---

## 🚀 Getting Started Checklist

1. ☐ Add bundle to `composer.json` autoload
2. ☐ Register bundle in `config/bundles.php`
3. ☐ Create `config/packages/web_base.yaml` with company data
4. ☐ Create `config/routes/web_base.yaml` for bundle routes
5. ☐ Run `composer dump-autoload`
6. ☐ Add `@web-base` alias to `webpack.config.js`
7. ☐ Import bundle assets in your `app.ts`
8. ☐ Create `content/_pages.php` with page metadata
9. ☐ Create your page templates in `templates/pages/`
10. ☐ Clear cache: `php bin/console cache:clear`
11. ☐ Build assets: `yarn build`

---

## 📖 Related Documentation

- [Usage Guide](./usage.md) - Detailed integration instructions
- [README](./readme.md) - Quick start and overview
- [Symfony Bundle Documentation](https://symfony.com/doc/current/bundles.html)
- [Twig Template Overriding](https://symfony.com/doc/current/bundles/override.html)
