<?php
declare(strict_types=1);

return [
  'index' => [
    'title' => 'UniSurf - Managed Webhosting, Serverbetrieb & IT ohne Stress',
    'description' =>
      'UniSurf bietet Services für einzigartige Ideen: Webentwicklung, Hosting und digitale Lösungen für Ihr Unternehmen.',
    'keywords' =>
      'UniSurf, Managed Webhosting, Webhosting, Hosting Anbieter, Hosting für Unternehmen, Hosting für Agenturen, Serveradministration, Serverbetrieb, Linux Server, Cloud Hosting, Hetzner Cloud, VPS, Dedicated Server, Managed Server, Wartung, Patch Management, Sicherheitsupdates, Hardening, Firewall, WAF, DDoS Schutz, Uptime Monitoring, 24/7 Monitoring, Alerting, Backups, Restore, Disaster Recovery, SLA, Performance Tuning, Caching, CDN, SSL, TLS, Let\'s Encrypt, Domains, DNS, E-Mail Hosting, SPF, DKIM, DMARC, Datenbanken, MySQL, PostgreSQL, PHP Hosting, Symfony Hosting, WordPress Hosting, Shop Hosting, Deployment, CI/CD, Git Deploy, Staging, Produktion, Proxmox, Docker, Kubernetes, Beratung, Support',
    'canonical' => '/',
    'robots' => 'index,follow',
    'og_image' => '/assets/og/home.jpg',
    'cms' => false,
    'nav' => true,
    'nav_label' => 'Home',
    'nav_order' => 10,
  ],
  'services' => [
    'title' => 'UniSurf - Services',
    'description' =>
      'Unsere Services: Webentwicklung, Hosting und digitale Lösungen für Ihr Unternehmen.',
    'keywords' => 'Services, Webentwicklung, Hosting, E-Commerce, Portale',
    'canonical' => '/services',
    'robots' => 'index,follow',
    'og_image' => '/assets/og/services.jpg',
    'cms' => false,
    'nav' => true,
    'nav_label' => 'Services',
    'nav_order' => 20,
  ],
  'entwicklung' => [
    'title' => 'UniSurf - Entwicklung',
    'description' =>
      'Professionelle Webentwicklung: Internetseiten, Webshops, Portale und skalierbare Plattformen.',
    'keywords' => 'Webentwicklung, Internetseiten, Webshops, Portale, E-Commerce, Plattformen',
    'canonical' => '/entwicklung',
    'robots' => 'index,follow',
    'og_image' => '/assets/og/entwicklung.jpg',
    'cms' => false,
    'nav' => false,
    'nav_label' => 'Entwicklung',
    'nav_order' => 30,
  ],
  'hosting' => [
    'title' => 'UniSurf - Hosting',
    'description' =>
      'Professionelles Web-Hosting: Cloud-basiert, sicher und zuverlässig mit individueller Betreuung.',
    'keywords' => 'Web-Hosting, Cloud-Hosting, Server, Infrastruktur, Sicherheit',
    'canonical' => '/hosting',
    'robots' => 'index,follow',
    'og_image' => '/assets/og/hosting.jpg',
    'cms' => false,
    'nav' => true,
    'nav_label' => 'Hosting',
    'nav_order' => 40,
  ],
  'kontakt' => [
    'title' => 'Kontaktieren Sie UniSurf – Beratung & Support',
    'description' =>
      'Kontaktieren Sie UniSurf für Hosting-Beratung, Migrationshilfe oder Managed-Services. Wir melden uns zeitnah mit einer pragmatischen Lösung.',
    'keywords' => 'Kontakt, Hosting Beratung, Support',
    'canonical' => '/kontakt',
    'robots' => 'index,follow',
    'og_image' => '/assets/og/kontakt.jpg',
    'cms' => false,
    'nav' => true,
    'nav_label' => 'Kontakt',
    'nav_order' => 50,
  ],
  'impressum' => [
    'title' => 'UniSurf - Impressum',
    'description' => 'Impressum und rechtliche Angaben von UniSurf.',
    'keywords' => 'Impressum, Anbieterkennzeichnung, Rechtliche Angaben',
    'canonical' => '/impressum',
    'robots' => 'index,follow',
    'og_image' => '/assets/og/impressum.jpg',
    'cms' => true,
    'nav' => false,
    'nav_label' => 'Impressum',
    'nav_order' => 60,
  ],
  'datenschutz' => [
    'title' => 'UniSurf - Datenschutzerklärung',
    'description' =>
      'Datenschutzerklärung: Informationen zur Verarbeitung personenbezogener Daten (DSGVO).',
    'keywords' => 'Datenschutzerklärung, DSGVO, Datenschutz, personenbezogene Daten',
    'canonical' => '/datenschutz',
    'robots' => 'index,follow',
    'og_image' => '/assets/og/datenschutz.jpg',
    'cms' => true,
    'nav' => false,
    'nav_label' => 'Datenschutzerklärung',
    'nav_order' => 70,
  ],
];
