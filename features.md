# Features Overview

This document provides a comprehensive overview of all features included in the web-base library.

## Backend Features

### Controllers

#### AbstractBaseController

- **Purpose**: Provides page metadata loading from `content/_pages.php`
- **Key Methods**:
  - `loadPageMetadata(string $slug): array` - Loads page configuration including:
    - Page title, description, keywords
    - Canonical URL
    - Robots meta directives
    - Open Graph image
    - Navigation settings

**Example Page Metadata:**

```php
[
    'title' => 'About Us - My Site',
    'description' => 'Learn about our company',
    'canonical' => '/about',
    'robots' => 'index,follow',
    'og_image' => '/assets/og/about.jpg',
    'nav' => true,
    'nav_label' => 'About',
    'nav_order' => 20,
]
```

### Services

#### AbstractFormService

Provides comprehensive form handling with security features:

**Rate Limiting**

- Configurable time windows (default: 1 hour)
- Minimum interval between submissions (default: 30 seconds)
- Maximum submissions per window (default: 10)
- Session-based tracking

**Security**

- Honeypot field detection
- CSRF protection (via Symfony forms)
- Session validation
- IP tracking (when combined with FormSubmissionMetaEntity)

**Helper Methods**

- `handleFormRequest()` - Bootstrap form handling
- `getHoneypotValue()` - Check for spam
- `rateLimitCheck()` - Verify submission rate
- `rateLimitTick()` - Record submission
- `makeRedirect()` - Create redirect responses

#### NavigationService

- **Purpose**: Builds navigation menus from page configuration
- **Features**:
  - Automatic navigation item detection
  - Customizable labels and URLs
  - Ordering support
  - Slug-based routing

**Returns:**

```php
[
    [
        'slug' => 'start',
        'label' => 'Home',
        'url' => '/',
        'order' => 10,
    ],
    // ... more items
]
```

#### MailManService

Professional email sending service with:

**Features**

- Twig template rendering (HTML + text)
- Theme-aware emails (light/dark mode support)
- Safe address handling with fallbacks
- Reply-to address support
- Automatic logging
- Error handling with fallbacks

**Methods**

- `sendTemplatedEmail()` - Send emails using templates
- `getFromAddress()` - Get configured from address
- `getToAddress()` - Get configured to address

### Entities

#### FormContactEntity

Base entity for contact form submissions:

**Fields**

- `name` - Contact name (max 120 chars)
- `emailAddress` - Email (validated, max 200 chars)
- `phone` - Phone number (optional, max 40 chars)
- `message` - Message text (10-5000 chars)
- `consent` - GDPR consent checkbox
- `copy` - Send copy to sender option
- `emailrep` - Honeypot field (not persisted)
- `createdAt` - Submission timestamp

**Special Features**

- Email to `Address` object conversion
- HTML-safe message rendering with `getMessageHtml()`
- Validation constraints included

#### FormSubmissionMetaEntity

Tracks submission metadata:

**Fields**

- `ip` - Client IP address
- `userAgent` - Browser user agent
- `time` - Submission time
- `host` - Server host

**Usage**: Link to form entities via OneToOne relationship

### Templates

#### Base Layout (`base.html.twig`)

Complete HTML5 template with:

- Responsive viewport settings
- SEO meta tags (title, description, keywords, robots)
- Canonical URLs
- Open Graph tags (title, description, image, type, URL)
- Twitter Card tags
- Favicon support
- Theme system integration
- Block structure for easy overriding

#### Partials

**Navbar (`_partials/navbar.html.twig`)**

- Responsive Bootstrap 5 navbar
- Collapsible mobile menu
- Dynamic navigation from `NavigationService`
- Theme toggle button
- Brand/logo support

**Footer (`_partials/footer.html.twig`)**

- Basic footer structure
- Copyright information
- Legal links (imprint, privacy policy)

#### Email Templates

**Contact Owner (`email/contact_owner.html.twig` + `.txt.twig`)**

- Formatted contact form notification to site owner
- Displays all form fields
- Styled HTML version + plain text fallback

**Contact Visitor (`email/contact_visitor.html.twig` + `.txt.twig`)**

- Confirmation email to form submitter
- Thank you message
- Copy of their submission
- Styled HTML version + plain text fallback

**Email Base (`email/base.html.twig`)**

- Email-safe HTML structure
- Theme-aware styling (light/dark)
- Responsive design
- Block structure for customization

### Testing

#### DatabaseTestCase

Base class for database-dependent tests:

**Features**

- Automatic schema creation/teardown
- Entity manager access
- Fixture loading support
- SQLite in-memory database support
- Works with Symfony's WebTestCase

**Methods**

- `getEntityManager()` - Get Doctrine entity manager
- `loadFixtures()` - Override to load test data
- `bootKernel()` - Automatic database setup

## Frontend Features

### TypeScript Utilities

#### Theme Toggle (`theme-toggle.ts`)

Complete light/dark mode implementation:

**Features**

- Three modes: light, dark, system
- localStorage persistence
- Smooth transitions
- CSS custom properties integration
- Bootstrap theme switching
- Dropdown menu integration
- Auto-detection of system preference

**Usage**: Automatically activates on page load

#### Navbar Shrink (`navbar-shrink.ts`)

Scroll-based navbar behavior:

**Features**

- Shrinks navbar on scroll
- Smooth transitions
- Configurable scroll threshold
- CSS class toggling
- Performance optimized (throttled)

#### Contact Form (`contact-form.ts`)

Client-side form handling:

**Features**

- Real-time validation
- Custom error messages
- AJAX submission support
- Loading states
- Success/error feedback
- Honeypot field handling

#### Contacts (`contacts.ts`)

Contact list functionality:

**Features**

- List rendering
- Filtering/sorting
- Responsive layout
- Click handlers

### SCSS Stylesheets

#### Variables (`_variables.scss`)

Comprehensive variable system:

**Includes**

- Brand colors
- Typography settings
- Spacing scale
- Border radius values
- Shadow definitions
- Transition timing
- Breakpoints
- Z-index layers

**Customizable Before Import:**

```scss
// Override before importing
$primary: #007bff;
$secondary: #6c757d;
@import '~@net-idea/web-base/frontend/styles/variables';
```

#### Theme System (`_theme.scss`, `_theme-light.scss`, `_theme-dark.scss`)

Modern theme implementation:

**Features**

- CSS custom properties
- Automatic mode switching
- Color scheme media query support
- Smooth transitions
- Component-level theming
- Background, text, and border colors
- Shadow and elevation system

**Color Scheme Support:**

- Light mode optimized
- Dark mode optimized
- System preference detection
- Manual toggle override

#### Base Styles (`_base.scss`)

Foundation styles:

**Includes**

- Modern CSS reset
- Typography baseline
- Link styles
- Focus states
- Selection colors
- Print styles
- Utility classes

#### Form Styles (`_forms.scss`, `_forms-light.scss`, `_forms-dark.scss`)

Professional form styling:

**Features**

- Input field styling
- Label positioning
- Error states
- Success states
- Disabled states
- Focus indicators
- Placeholder styling
- Checkbox/radio custom styling
- Theme-specific colors

#### App Styles (`app.scss`)

Main stylesheet that:

**Includes**

- Bootstrap 5 integration
- Variable imports
- Component imports
- Layout utilities
- Custom components
- Responsive utilities

#### Font Styles (`fonts.scss`)

Self-hosted web fonts:

**Includes**

- @font-face declarations
- Multiple weights
- Multiple styles (normal, italic)
- Multiple formats (woff2, woff, ttf)
- Font-display: swap for performance
- Preload hints

**Fonts Configured:**

- Montserrat (sans-serif, various weights)
- Roboto Slab (serif, various weights)

## Integration Points

### Symfony Integration

- Services auto-wiring support
- Twig template path registration
- Doctrine entity mapping
- Form type integration
- Mailer configuration
- Translation support

### Bootstrap 5 Integration

- Full Bootstrap 5 SCSS imports
- Custom theme colors
- Component overrides
- Utility class extensions

### Webpack Encore Integration

- TypeScript support
- Sass compilation
- Asset versioning
- Source maps
- Production optimization

## Browser Support

### CSS Features

- CSS Custom Properties (CSS Variables)
- CSS Grid
- Flexbox
- Media Queries (including `prefers-color-scheme`)

### JavaScript Features

- ES2020+ syntax
- localStorage API
- IntersectionObserver
- Async/await

### Minimum Versions

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Performance Features

### Backend

- Database query optimization
- Session management
- Rate limiting to prevent abuse
- Email template caching

### Frontend

- CSS minification (production)
- JavaScript bundling and minification
- Font optimization (woff2 format)
- Lazy loading support
- Tree shaking (unused code removal)
- Code splitting potential

## Security Features

### Backend

- CSRF protection via Symfony forms
- Rate limiting on form submissions
- Honeypot spam protection
- Email validation
- XSS prevention (htmlentities)
- SQL injection protection (Doctrine ORM)
- Session validation

### Frontend

- Content Security Policy compatible
- XSS prevention
- Safe HTML rendering
- Input sanitization

## Accessibility Features

- Semantic HTML5
- ARIA labels where appropriate
- Keyboard navigation support
- Focus indicators
- Skip links support
- Form label associations
- Error message associations
- Color contrast compliance (WCAG AA)

## Internationalization

- Twig translation filter support
- Configurable messages
- UTF-8 encoding throughout
- RTL layout support potential

## Documentation

- Comprehensive README
- Detailed USAGE guide
- CHANGELOG with version history
- Inline code documentation
- PHPDoc comments
- TypeScript type definitions
- Example configurations

---

For detailed usage instructions, see [usage.md](usage.md).
For installation instructions, see [readme.md](readme.md).
