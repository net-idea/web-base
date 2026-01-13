# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

#### Backend
- `AbstractBaseController` - Base controller with page metadata loading from `content/_pages.php`
- `AbstractFormService` - Abstract service for form handling with:
  - Rate limiting (configurable time windows and submission limits)
  - Honeypot field detection
  - Session management helpers
  - Redirect helpers
- `NavigationService` - Dynamic navigation builder from page configuration
- `MailManService` - Email service for sending templated emails with:
  - HTML and text template support
  - Theme-aware emails (light/dark mode)
  - Safe address handling with fallbacks
  - Reply-to support
- `FormContactEntity` - Base entity for contact form submissions with:
  - Standard fields (name, email, phone, message)
  - Consent tracking
  - Honeypot field
  - Copy-to-sender option
  - HTML-safe message rendering
- `FormSubmissionMetaEntity` - Base entity for form submission metadata (IP, user agent, timestamp, host)
- `DatabaseTestCase` - Base test case for tests requiring database access
- Twig templates:
  - `base.html.twig` - Base layout with SEO meta tags, Open Graph, Twitter Cards
  - `_partials/navbar.html.twig` - Responsive navbar with theme toggle
  - `_partials/footer.html.twig` - Footer template
  - Email templates for contact forms (HTML and text versions)
- Example page configuration file (`content/_pages.example.php`)
- PHPUnit test bootstrap files

#### Frontend
- TypeScript utilities:
  - `theme-toggle.ts` - Light/dark theme switcher with localStorage persistence
  - `navbar-shrink.ts` - Scroll-based navbar size adjustment
  - `contact-form.ts` - Client-side contact form validation and handling
  - `contacts.ts` - Contact list functionality
- SCSS stylesheets:
  - `_variables.scss` - Customizable SCSS variables (colors, spacing, typography)
  - `_base.scss` - Base styles and resets
  - `_theme.scss` - Theme system with CSS custom properties
  - `_theme-light.scss` - Light theme colors
  - `_theme-dark.scss` - Dark theme colors
  - `_forms.scss` - Form component styles
  - `_forms-light.scss` - Light theme form styles
  - `_forms-dark.scss` - Dark theme form styles
  - `app.scss` - Main application stylesheet with Bootstrap integration
  - `fonts.scss` - Font definitions and @font-face rules

#### Configuration
- `composer.json` - Backend dependencies with Symfony 6.4, 7.0, and 8.0 support
- `package.json` - Frontend dependencies with TypeScript and Bootstrap
- Root-level `composer.json` for easy installation
- Root-level `package.json` for npm/yarn installation
- Comprehensive README.md with usage instructions
- Detailed USAGE.md with integration examples
- Updated .gitignore for backend/frontend structure

### Changed
- Namespace changed from `App\` to `NetIdea\WebBase\` for all PHP classes
- All entities made reusable with `#[ORM\MappedSuperclass]` attribute
- Email service simplified for general-purpose use
- Templates made configurable with block overrides

### Notes
- Extracted from [UniSurf](https://github.com/net-idea/unisurf) project
- Designed to be reusable across multiple Symfony projects
- Compatible with Symfony 6.4, 7.0, and 8.0
- Requires PHP 8.2 or higher
- Frontend requires Node.js 18+ and Bootstrap 5.3+

[Unreleased]: https://github.com/net-idea/web-base/compare/v0.0.0...HEAD
