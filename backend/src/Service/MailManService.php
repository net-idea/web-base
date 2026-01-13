<?php

declare(strict_types=1);

namespace NetIdea\WebBase\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment as Twig;

/**
 * Base service for sending emails with Twig templates
 */
class MailManService
{
    private const THEME_STORAGE_KEY = 'theme';

    public function __construct(
        private MailerInterface $mailer,
        private Twig $twig,
        private string $fromAddress,
        private string $fromName,
        private string $toAddress,
        private string $toName,
        private LoggerInterface $logger,
        private RequestStack $requestStack,
    ) {
    }

    /**
     * Send an email using a Twig template
     *
     * @param array<string, mixed> $context
     * @throws TransportExceptionInterface
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendTemplatedEmail(
        string $to,
        string $subject,
        string $textTemplate,
        string $htmlTemplate,
        array $context = [],
        ?string $replyTo = null,
        ?string $toName = null,
    ): void {
        $from = $this->makeAddressOrFallback($this->fromAddress, $this->fromName, 'no-reply@localhost');
        $toAddress = $this->makeAddressOrFallback($to, $toName ?? '', $to);

        $theme = $this->getEmailTheme();
        $context['theme'] = $theme;

        try {
            $text = $this->twig->render($textTemplate, $context);
            $html = $this->twig->render($htmlTemplate, $context);

            $email = (new Email())
                ->from($from)
                ->to($toAddress)
                ->subject($subject)
                ->text($text)
                ->html($html);

            if ($replyTo) {
                try {
                    $email->replyTo(new Address($replyTo));
                } catch (\Throwable $e) {
                    $this->logger->warning('Invalid replyTo email; skipping replyTo header', [
                        'replyTo'   => $replyTo,
                        'exception' => $e,
                    ]);
                }
            }

            $this->mailer->send($email);
            $this->logger->info('Email sent successfully', [
                'to'      => $toAddress->getAddress(),
                'subject' => $subject,
                'theme'   => $theme,
            ]);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Mailer send failed: ' . $e->getMessage(), ['exception' => $e]);

            throw $e;
        }
    }

    /**
     * Get the configured from address
     */
    public function getFromAddress(): string
    {
        return $this->fromAddress;
    }

    /**
     * Get the configured to address (owner email)
     */
    public function getToAddress(): string
    {
        return $this->toAddress;
    }

    /**
     * Safe helper: try to create an Address from the provided email/name and
     * fall back to a sane default if the value is empty or invalid.
     */
    protected function makeAddressOrFallback(string $email, string $name, string $fallback): Address
    {
        $email = trim($email);

        if ('' === $email) {
            $this->logger->warning(sprintf('Email is empty, using fallback <%s>', $fallback));

            return new Address($fallback, $name);
        }

        try {
            return new Address($email, $name);
        } catch (\Throwable $e) {
            $this->logger->warning(
                sprintf('Invalid email "%s", using fallback <%s>: %s', $email, $fallback, $e->getMessage()),
                ['exception' => $e],
            );

            return new Address($fallback, $name);
        }
    }

    /**
     * Determine which email theme to use based on user's preference
     */
    protected function getEmailTheme(): string
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return 'light';
        }

        $session = $request->getSession();
        $storedTheme = $session->get(self::THEME_STORAGE_KEY);

        // If user explicitly chose dark or light, use that
        if ('dark' === $storedTheme) {
            return 'dark';
        }

        if ('light' === $storedTheme) {
            return 'light';
        }

        // Default to light theme for emails
        return 'light';
    }
}
