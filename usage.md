# Usage Guide

This guide provides detailed instructions for integrating the `web-base` library into your Symfony project.

## Table of Contents

- [Installation](#installation)
- [Backend Integration](#backend-integration)
- [Frontend Integration](#frontend-integration)
- [Configuration Examples](#configuration-examples)
- [Common Use Cases](#common-use-cases)

## Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and Yarn (or npm)
- Symfony 6.4, 7.0, or 8.0

### Backend Installation

1. Add the library to your Symfony project's `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/net-idea/web-base"
    }
  ],
  "require": {
    "net-idea/web-base": "dev-main"
  }
}
```

2. Run Composer install:

```bash
composer install
```

3. Register the namespace in your autoloader (if not automatically detected):

```json
{
  "autoload": {
    "psr-4": {
      "NetIdea\\WebBase\\": "vendor/net-idea/web-base/backend/src/"
    }
  }
}
```

### Frontend Installation

1. Add the library to your `package.json`:

```json
{
  "dependencies": {
    "@net-idea/web-base": "github:net-idea/web-base"
  }
}
```

2. Run Yarn install:

```bash
yarn install
```

## Backend Integration

### Setting Up Page Metadata

1. Create `content/_pages.php` in your project root:

```php
<?php
declare(strict_types=1);

return [
    'start' => [
        'title' => 'Home - My Website',
        'description' => 'Welcome to my website',
        'keywords' => 'web, symfony, home',
        'canonical' => '/',
        'robots' => 'index,follow',
        'og_image' => '/assets/og/home.jpg',
        'nav' => true,
        'nav_label' => 'Home',
        'nav_order' => 10,
    ],
    'about' => [
        'title' => 'About Us - My Website',
        'description' => 'Learn about our company',
        'canonical' => '/about',
        'robots' => 'index,follow',
        'og_image' => '/assets/og/about.jpg',
        'nav' => true,
        'nav_label' => 'About',
        'nav_order' => 20,
    ],
    'contact' => [
        'title' => 'Contact - My Website',
        'description' => 'Get in touch with us',
        'canonical' => '/contact',
        'robots' => 'index,follow',
        'og_image' => '/assets/og/contact.jpg',
        'nav' => true,
        'nav_label' => 'Contact',
        'nav_order' => 30,
    ],
];
```

### Using the Base Controller

Create a controller extending `AbstractBaseController`:

```php
<?php

namespace App\Controller;

use NetIdea\WebBase\Controller\AbstractBaseController;
use NetIdea\WebBase\Service\NavigationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractBaseController
{
    public function __construct(
        private NavigationService $navigationService
    ) {
    }

    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->page('start');
    }

    #[Route('/{slug}', name: 'page', priority: -1)]
    public function page(string $slug = 'start'): Response
    {
        $pageMeta = $this->loadPageMetadata($slug);
        $navItems = $this->navigationService->getItems();

        return $this->render('pages/content.html.twig', [
            'slug' => $slug,
            'pageMeta' => $pageMeta,
            'navItems' => $navItems,
        ]);
    }
}
```

### Using the Form Service

Create a custom form service:

```php
<?php

namespace App\Service;

use App\Entity\ContactForm;
use App\Form\ContactFormType;
use NetIdea\WebBase\Service\AbstractFormService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContactFormService extends AbstractFormService
{
    private FormInterface $form;

    public function __construct(
        private FormFactoryInterface $formFactory,
        private LoggerInterface $logger,
        private RequestStack $requestStack,
        private UrlGeneratorInterface $urls,
    ) {
        $this->form = $this->formFactory->create(ContactFormType::class);
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }

    public function handleContactSubmission(): ?array
    {
        $result = $this->handleFormRequest($this->requestStack);

        if (!$result) {
            return null;
        }

        [$request, $form, $session] = $result;

        // Check honeypot field
        if ('' !== $this->getHoneypotValue($form, 'emailrep')) {
            $this->logger->warning('Contact form honeypot triggered');
            return ['success' => false, 'error' => 'spam'];
        }

        // Rate limiting
        $rateCheck = $this->rateLimitCheck(
            $session,
            'contact_form',
            self::RATE_MIN_INTERVAL_SECONDS,
            self::RATE_MAX_PER_WINDOW
        );

        if ($rateCheck['blocked']) {
            $this->logger->warning('Contact form rate limit exceeded');
            return ['success' => false, 'error' => 'rate_limit'];
        }

        // Validate form
        if (!$form->isValid()) {
            return ['success' => false, 'error' => 'validation'];
        }

        // Process the form data
        $data = $form->getData();

        // Save to database, send email, etc.

        // Update rate limit
        $this->rateLimitTick($session, 'contact_form', $rateCheck['times'], $rateCheck['now']);

        return ['success' => true];
    }
}
```

### Using the Mail Service

Configure the mail service in `config/services.yaml`:

```yaml
services:
  NetIdea\WebBase\Service\MailManService:
    arguments:
      $fromAddress: '%env(MAIL_FROM_ADDRESS)%'
      $fromName: '%env(MAIL_FROM_NAME)%'
      $toAddress: '%env(MAIL_TO_ADDRESS)%'
      $toName: '%env(MAIL_TO_NAME)%'
```

Add environment variables to `.env`:

```env
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="My Website"
MAIL_TO_ADDRESS=contact@example.com
MAIL_TO_NAME="Contact Form"
```

Use the service in your controller:

```php
<?php

namespace App\Controller;

use NetIdea\WebBase\Service\MailManService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    public function __construct(
        private MailManService $mailService
    ) {
    }

    public function sendContactEmail(ContactForm $contact): void
    {
        $this->mailService->sendTemplatedEmail(
            to: $this->mailService->getToAddress(),
            subject: 'New Contact Form Submission',
            textTemplate: 'email/contact.txt.twig',
            htmlTemplate: 'email/contact.html.twig',
            context: [
                'contact' => $contact,
            ],
            replyTo: $contact->getEmailAddress()
        );
    }
}
```

### Using Base Templates

Copy the base templates to your project or extend them:

```twig
{# templates/base.html.twig #}
{% extends '@WebBase/base.html.twig' %}

{% block stylesheets %}
    {{ parent() }}
    {# Add your custom styles here #}
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    {# Add your custom scripts here #}
{% endblock %}
```

## Frontend Integration

### Webpack Encore Setup

Update your `webpack.config.js`:

```javascript
const Encore = require('@symfony/webpack-encore');

Encore.setOutputPath('public/build/')
  .setPublicPath('/build')

  // Entry points
  .addEntry('app', './assets/app.ts')

  // Enable TypeScript
  .enableTypeScriptLoader()

  // Enable Sass
  .enableSassLoader()

  // Enable source maps in dev
  .enableSourceMaps(!Encore.isProduction())

  // Enable versioning in production
  .enableVersioning(Encore.isProduction())

  // Single runtime chunk
  .enableSingleRuntimeChunk()

  // Configure Babel
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = 'usage';
    config.corejs = 3;
  });

module.exports = Encore.getWebpackConfig();
```

### TypeScript Configuration

Create or update `tsconfig.json`:

```json
{
  "compilerOptions": {
    "target": "ES2020",
    "module": "ESNext",
    "lib": ["ES2020", "DOM"],
    "moduleResolution": "node",
    "strict": true,
    "esModuleInterop": true,
    "skipLibCheck": true,
    "forceConsistentCasingInFileNames": true,
    "resolveJsonModule": true,
    "allowSyntheticDefaultImports": true
  },
  "include": ["assets/**/*", "node_modules/@net-idea/web-base/frontend/**/*"],
  "exclude": ["node_modules", "public", "var"]
}
```

### Main Application Entry

Create `assets/app.ts`:

```typescript
// Import Bootstrap and Popper
import 'bootstrap/dist/js/bootstrap.bundle';

// Import web-base styles
import '../node_modules/@net-idea/web-base/frontend/styles/app.scss';

// Import web-base scripts
import '../node_modules/@net-idea/web-base/frontend/scripts/theme-toggle';
import '../node_modules/@net-idea/web-base/frontend/scripts/navbar-shrink';
import '../node_modules/@net-idea/web-base/frontend/scripts/contact-form';

// Your custom scripts
import './custom-script';
```

### SCSS Customization

Create `assets/styles/app.scss`:

```scss
// Import web-base variables (customize before importing)
@import '~@net-idea/web-base/frontend/styles/variables';

// Override variables if needed
$primary: #007bff;
$secondary: #6c757d;

// Import Bootstrap
@import '~bootstrap/scss/bootstrap';

// Import web-base styles
@import '~@net-idea/web-base/frontend/styles/base';
@import '~@net-idea/web-base/frontend/styles/theme';
@import '~@net-idea/web-base/frontend/styles/forms';

// Your custom styles
.my-custom-class {
  color: $primary;
}
```

## Common Use Cases

### Creating a Contact Form

1. Create the entity:

```php
<?php

namespace App\Entity;

use NetIdea\WebBase\Entity\FormContactEntity as BaseFormContactEntity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'contact_form')]
class ContactForm extends BaseFormContactEntity
{
    // Add any custom fields here
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $company = null;

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): self
    {
        $this->company = $company;
        return $this;
    }
}
```

2. Create the form type:

```php
<?php

namespace App\Form;

use App\Entity\ContactForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'required' => true,
            ])
            ->add('emailAddress', EmailType::class, [
                'label' => 'Email',
                'required' => true,
            ])
            ->add('phone', TelType::class, [
                'label' => 'Phone',
                'required' => false,
            ])
            ->add('company', TextType::class, [
                'label' => 'Company',
                'required' => false,
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'required' => true,
            ])
            ->add('consent', CheckboxType::class, [
                'label' => 'I agree to the privacy policy',
                'required' => true,
            ])
            ->add('copy', CheckboxType::class, [
                'label' => 'Send me a copy',
                'required' => false,
            ])
            ->add('emailrep', TextType::class, [
                'label' => false,
                'required' => false,
                'mapped' => false,
                'attr' => ['style' => 'display:none;'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactForm::class,
        ]);
    }
}
```

3. Create the controller:

```php
<?php

namespace App\Controller;

use App\Service\ContactFormService;
use NetIdea\WebBase\Controller\AbstractBaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractBaseController
{
    public function __construct(
        private ContactFormService $contactFormService
    ) {
    }

    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        $form = $this->contactFormService->getForm();
        $result = $this->contactFormService->handleContactSubmission();

        if ($result && $result['success']) {
            $this->addFlash('success', 'Thank you! Your message has been sent.');
            return $this->redirectToRoute('contact');
        }

        if ($result && !$result['success']) {
            $this->addFlash('error', 'An error occurred. Please try again.');
        }

        return $this->render('pages/contact.html.twig', [
            'form' => $form->createView(),
            'pageMeta' => $this->loadPageMetadata('contact'),
        ]);
    }
}
```

## Troubleshooting

### Templates Not Found

If you get template not found errors, register the template path in `config/packages/twig.yaml`:

```yaml
twig:
  paths:
    '%kernel.project_dir%/vendor/net-idea/web-base/backend/templates': WebBase
```

### Autoloading Issues

If classes are not found, run:

```bash
composer dump-autoload
```

### Frontend Build Errors

If you encounter module resolution errors:

```bash
yarn install --force
rm -rf node_modules/.cache
yarn encore dev
```

## Support

For issues, questions, or contributions, please visit:
https://github.com/net-idea/web-base

## Page Templates

### Content Template (`templates/pages/content.html.twig`)

Generic template for rendering Markdown content. Use this for static pages loaded from `.md` files.

**Example usage:**

```php
// In your controller
public function page(string $slug): Response
{
    $pageMeta = $this->loadPageMetadata($slug);

    // Load Markdown content
    $contentFile = $this->getParameter('kernel.project_dir') . '/content/' . $slug . '.md';
    $markdown = file_exists($contentFile) ? file_get_contents($contentFile) : '';
    $parsedown = new \Parsedown();
    $content = $parsedown->text($markdown);

    return $this->render('@WebBase/pages/content.html.twig', [
        'pageMeta' => $pageMeta,
        'content' => $content,
        'siteName' => 'My Company'
    ]);
}
```

### Contact Form Template (`templates/pages/contact.html.twig`)

Fully configurable contact form template. All text can be customized via the `_pages.php` configuration.

**Configurable parameters in `_pages.php`:**

```php
'contact' => [
    'title' => 'Contact Us - My Company',
    'description' => 'Get in touch with us',
    // Masthead (hero section)
    'masthead_subheading' => 'Contact Form',
    'masthead_heading' => 'We are here for you',
    'masthead_description' => 'Have questions about our services? Write to us – we will get back to you personally.',
    // Form section
    'form_heading' => 'Send a Message',
    'form_subheading' => 'Tell us about your project or current situation. We listen and look at how we can support you.',
    // Buttons
    'submit_button_text' => 'Submit',
    'show_whatsapp' => false,  // Show WhatsApp button
    'whatsapp_button_text' => 'Contact via WhatsApp',
    // Privacy notice
    'privacy_heading' => 'Privacy',
    'privacy_text' => 'Your information will be used solely to process your inquiry and will be deleted afterwards.',
    'privacy_link' => true,  // Show link to privacy policy
    // Other standard fields
    'canonical' => '/contact',
    'robots' => 'index,follow',
],
```

**Example controller:**

```php
use App\Form\ContactFormType;
use App\Service\ContactFormService;
use NetIdea\WebBase\Controller\AbstractBaseController;

class ContactController extends AbstractBaseController
{
    public function __construct(
        private ContactFormService $contactFormService
    ) {}

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        $pageMeta = $this->loadPageMetadata('contact');
        $form = $this->contactFormService->getForm();

        // Handle form submission
        $result = $this->contactFormService->handleContactSubmission();

        if ($result && $result['success']) {
            $this->addFlash('success', 'Thank you! Your message has been sent.');
            return $this->redirectToRoute('app_contact');
        }

        return $this->render('@WebBase/pages/contact.html.twig', [
            'pageMeta' => $pageMeta,
            'form' => $form->createView(),
            'siteName' => 'My Company',
        ]);
    }
}
```

## Example Content Files

The library includes example content files in `backend/content/`:

- `index.md` - Example homepage content
- `impressum.example.md` - Example imprint/legal page (German)
- `datenschutz.example.md` - Example privacy policy (German)

Copy these files and customize them for your project:

```bash
cp vendor/net-idea/web-base/backend/content/index.md content/
cp vendor/net-idea/web-base/backend/content/impressum.example.md content/impressum.md
cp vendor/net-idea/web-base/backend/content/datenschutz.example.md content/datenschutz.md
```

Then edit them with your company-specific information.
