# web-base

Shared library for Symfony-based websites and web applications. This library provides reusable backend (PHP) and frontend (TypeScript/SCSS) code extracted from the [UniSurf](https://github.com/net-idea/unisurf) project.

## Overview

This library contains:

- **Backend**: Reusable PHP code including abstract controllers, services, entities, and Twig templates
- **Frontend**: Reusable TypeScript scripts and SCSS styles for Bootstrap-based UIs

## Structure

```
backend/
├── src/                    # PHP source code
│   ├── Controller/         # Abstract controllers
│   ├── Service/           # Services (forms, mail, navigation)
│   ├── Entity/            # Base entities
│   ├── Form/              # Form types
│   └── Repository/        # Repositories
├── templates/             # Twig templates
│   ├── _partials/         # Reusable template partials
│   ├── email/             # Email templates
│   └── pages/             # Page templates
├── content/               # Content structure files
│   └── _pages.example.php # Example page configuration
├── tests/                 # PHPUnit tests
└── composer.json          # Backend dependencies

frontend/
├── scripts/               # TypeScript utilities
│   ├── contact-form.ts   # Contact form handling
│   ├── contacts.ts       # Contact list functionality
│   ├── navbar-shrink.ts  # Navbar scroll behavior
│   └── theme-toggle.ts   # Light/dark theme switcher
├── styles/                # SCSS stylesheets
│   ├── _base.scss        # Base styles
│   ├── _forms.scss       # Form styles
│   ├── _theme.scss       # Theme styles
│   ├── _variables.scss   # SCSS variables
│   ├── app.scss          # Main application styles
│   └── fonts.scss        # Font definitions
└── package.json           # Frontend dependencies
```

## Installation

### Backend (Composer)

Add the library to your Symfony project:

```bash
cd backend
composer install
```

In your Symfony project's `composer.json`, add:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../path/to/web-base/backend"
    }
  ],
  "require": {
    "net-idea/web-base-backend": "*"
  }
}
```

### Frontend (Yarn)

Install frontend dependencies:

```bash
cd frontend
yarn install
```

In your project's `package.json`, add:

```json
{
  "dependencies": {
    "@net-idea/web-base-frontend": "file:../path/to/web-base/frontend"
  }
}
```

## Usage

### Backend

#### Using Abstract Controllers

Extend the `AbstractBaseController` to get page metadata loading:

```php
<?php

namespace App\Controller;

use NetIdea\WebBase\Controller\AbstractBaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyController extends AbstractBaseController
{
    #[Route('/{slug}', name: 'page')]
    public function page(string $slug = ''): Response
    {
        $pageMeta = $this->loadPageMetadata($slug);
        
        return $this->render('pages/index.html.twig', [
            'slug' => $slug,
            'pageMeta' => $pageMeta,
        ]);
    }
}
```

#### Using Form Services

Extend `AbstractFormService` for form handling with rate limiting and honeypot protection:

```php
<?php

namespace App\Service;

use NetIdea\WebBase\Service\AbstractFormService;
use Symfony\Component\Form\FormInterface;

class MyFormService extends AbstractFormService
{
    public function getForm(): FormInterface
    {
        // Return your form instance
    }
    
    public function handleSubmission()
    {
        $result = $this->handleFormRequest($this->requestStack);
        
        if (!$result) {
            return null;
        }
        
        [$request, $form, $session] = $result;
        
        // Check honeypot
        if ('' !== $this->getHoneypotValue($form)) {
            // Spam detected
            return;
        }
        
        // Rate limiting
        $rateCheck = $this->rateLimitCheck($session, 'my_form', 30, 10);
        if ($rateCheck['blocked']) {
            // Too many submissions
            return;
        }
        
        // Process form...
    }
}
```

#### Using Navigation Service

The `NavigationService` reads navigation items from `content/_pages.php`:

```php
<?php

namespace App\Controller;

use NetIdea\WebBase\Service\NavigationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyController extends AbstractController
{
    public function __construct(private NavigationService $navigationService)
    {
    }
    
    public function index()
    {
        $navItems = $this->navigationService->getItems();
        
        return $this->render('pages/index.html.twig', [
            'navItems' => $navItems,
        ]);
    }
}
```

#### Using Mail Service

Send templated emails:

```php
<?php

use NetIdea\WebBase\Service\MailManService;

class MyController
{
    public function __construct(private MailManService $mailService)
    {
    }
    
    public function sendEmail()
    {
        $this->mailService->sendTemplatedEmail(
            to: 'recipient@example.com',
            subject: 'Hello',
            textTemplate: 'email/my_email.txt.twig',
            htmlTemplate: 'email/my_email.html.twig',
            context: ['name' => 'John'],
            replyTo: 'sender@example.com',
            toName: 'John Doe'
        );
    }
}
```

#### Setting up Content Pages

Create `content/_pages.php` in your project root (use `backend/content/_pages.example.php` as reference):

```php
<?php
declare(strict_types=1);

return [
    'start' => [
        'title' => 'Home - My Website',
        'description' => 'Welcome to my website',
        'canonical' => '/',
        'robots' => 'index,follow',
        'og_image' => '/assets/og/home.jpg',
        'nav' => true,
        'nav_label' => 'Home',
        'nav_order' => 10,
    ],
    // Add more pages...
];
```

### Frontend

#### Using TypeScript Utilities

Import and use the provided TypeScript utilities:

```typescript
// In your main app.ts or index.ts
import '../node_modules/@net-idea/web-base-frontend/scripts/theme-toggle';
import '../node_modules/@net-idea/web-base-frontend/scripts/navbar-shrink';
import '../node_modules/@net-idea/web-base-frontend/scripts/contact-form';
```

#### Using SCSS Styles

Import the base styles in your main SCSS file:

```scss
// Import variables first
@import '~@net-idea/web-base-frontend/styles/variables';

// Import Bootstrap setup
@import '~bootstrap/scss/functions';
@import '~bootstrap/scss/variables';
@import '~bootstrap/scss/variables-dark';

// Import web-base styles
@import '~@net-idea/web-base-frontend/styles/base';
@import '~@net-idea/web-base-frontend/styles/theme';
@import '~@net-idea/web-base-frontend/styles/forms';

// Your custom styles
```

#### Webpack Encore Configuration

Example configuration for Webpack Encore:

```javascript
const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.ts')
    .enableSassLoader()
    .enableTypeScriptLoader()
    .enableSingleRuntimeChunk()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();
```

## Features

### Backend Features

- **Abstract Base Controller**: Page metadata loading from configuration
- **Abstract Form Service**: Form handling with rate limiting and honeypot protection
- **Navigation Service**: Dynamic navigation from page configuration
- **Mail Service**: Templated email sending with theme support
- **Base Entities**: Reusable entity classes for forms and metadata
- **Email Templates**: Professional HTML and text email templates

### Frontend Features

- **Theme Toggle**: Light/dark mode switcher with localStorage persistence
- **Navbar Shrink**: Scroll-based navbar size adjustment
- **Contact Form**: Client-side form validation and handling
- **SCSS Variables**: Customizable color schemes and spacing
- **Bootstrap Integration**: Bootstrap 5 compatible styles

## Namespace

All PHP classes use the `NetIdea\WebBase` namespace to avoid conflicts with application code.

## Testing

Backend tests use PHPUnit:

```bash
cd backend
vendor/bin/phpunit
```

## Requirements

### Backend
- PHP 8.2 or higher
- Symfony 6.4, 7.0, or 8.0
- Doctrine ORM
- Twig

### Frontend
- Node.js 18+
- Yarn or npm
- TypeScript 5+
- Sass
- Bootstrap 5.3+

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Contributing

This library is extracted from the [UniSurf](https://github.com/net-idea/unisurf) project. Contributions are welcome!

## Credits

Developed by net-idea for shared use across Symfony projects.
