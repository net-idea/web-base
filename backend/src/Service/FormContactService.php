<?php
declare(strict_types=1);

namespace NetIdea\WebBase\Service;

use NetIdea\WebBase\Entity\FormContactEntity;
use NetIdea\WebBase\Entity\FormSubmissionMetaEntity;
use NetIdea\WebBase\Form\FormContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class FormContactService extends AbstractFormService
{
    private const string SESSION_RATE_KEY = 'cf_times';

    private ?FormInterface $form = null;

    public function __construct(
        private readonly FormFactoryInterface $forms,
        private readonly RequestStack $requests,
        private readonly MailManService $mailMan,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function getForm(): FormInterface
    {
        if (null === $this->form) {
            $this->form = $this->forms->create(FormContactType::class, new FormContactEntity());
        }

        return $this->form;
    }

    /**
     * Handle contact form submission via AJAX/API.
     */
    public function handleAjax(): JsonResponse
    {
        $boot = $this->handleFormRequest($this->requests);

        if (null === $boot) {
            return new JsonResponse(['status' => 'error', 'message' => 'No data submitted'], 400);
        }

        [$request, $form, $session] = $boot;

        // Centralized rate limit
        $rl = $this->rateLimitCheck(
            $session,
            self::SESSION_RATE_KEY,
            self::RATE_MIN_INTERVAL_SECONDS,
            self::RATE_MAX_PER_WINDOW,
            self::RATE_WINDOW_SECONDS,
        );

        if ($rl['blocked']) {
            return new JsonResponse(
                [
                    'status'  => 'error',
                    'code'    => 'rate',
                    'message' => 'Sie sind etwas zu schnell unterwegs. Bitte warten Sie einen Moment und versuchen Sie es dann erneut. Sollte das Problem weiterhin bestehen, kontaktieren Sie uns gerne direkt per E‑Mail.',
                ],
                429,
            );
        }

        // Honeypot check
        $honey = trim($this->getHoneypotValue($form, 'website'));

        /** @var FormContactEntity $contactForm */
        $contactForm = $form->getData();

        if ('' !== $honey || '' !== trim((string) $contactForm->getEmailrep())) {
            $this->rateLimitTickNow($session, self::SESSION_RATE_KEY);

            // Pretend success
            return new JsonResponse([
                'status'  => 'success',
                'message' => 'Vielen Dank für Ihre Nachricht. Wir haben diese erhalten. Sollten Sie innerhalb von 48 Stunden keine Antwort von uns erhalten, melden Sie sich bitte erneut – gelegentlich kann auch digital etwas verloren gehen.',
            ]);
        }

        if (!$form->isValid()) {
            $hasTechnical = false;
            $hasInput = false;
            $errors = $this->getFormErrors($form);

            foreach ($errors as $field => $messages) {
                foreach ($messages as $msg) {
                    if (
                        false !== stripos($msg, 'token') ||
                        false !== stripos($msg, 'technisch') ||
                        false !== stripos($msg, 'CSRF')
                    ) {
                        $hasTechnical = true;
                    } else {
                        $hasInput = true;
                    }
                }
            }

            $message = '';

            if (true === $hasTechnical) {
                $message .=
                  'Leider ist ein technischer Fehler aufgetreten. Bitte laden Sie die Seite neu und versuchen Sie es erneut. Sollte das Problem weiterhin bestehen, schreiben Sie uns gerne direkt eine E‑Mail.';
            }

            if (true === $hasInput) {
                $message .=
                  ('' !== $message ? ' ' : '') .
                  'Bitte geben Sie Ihren Namen, eine gültige E‑Mail-Adresse und eine aussagekräftige Nachricht an, damit wir Ihr Anliegen bestmöglich bearbeiten können.';
            }

            if ('' === $message) {
                $message =
                  'Entschuldigung, es ist ein Fehler aufgetreten. Bitte überprüfen Sie Ihre Eingaben und versuchen Sie es erneut.';
            }

            return new JsonResponse(
                [
                    'status'  => 'error',
                    'code'    => 'invalid',
                    'message' => $message,
                    'errors'  => $errors,
                ],
                422,
            );
        }

        // Prepare meta-data
        $meta = (new FormSubmissionMetaEntity())
            ->setIp((string) $request->server->get('REMOTE_ADDR', ''))
            ->setUserAgent((string) $request->server->get('HTTP_USER_AGENT', ''))
            ->setTime(date('c'))
            ->setHost($request->getHost());
        $contactForm->setMeta($meta);

        // Database persistence (optional)
        try {
            $this->em->persist($contactForm);
            $this->em->flush();
        } catch (\Exception $dbException) {
            error_log('Contact form database error: ' . $dbException->getMessage());
        }

        // Send email
        try {
            $this->mailMan->sendContactForm($contactForm);
        } catch (TransportExceptionInterface) {
            return new JsonResponse(
                [
                    'status'  => 'error',
                    'code'    => 'mail',
                    'message' => 'Leider konnten wir Ihre Nachricht technisch nicht übermitteln. Bitte versuchen Sie es später erneut oder schreiben Sie uns direkt eine E‑Mail. Wir entschuldigen uns für die Unannehmlichkeiten.',
                ],
                500,
            );
        }

        // Success
        $this->rateLimitTickNow($session, self::SESSION_RATE_KEY);

        return new JsonResponse([
            'status'  => 'success',
            'message' => 'Vielen Dank für Ihre Nachricht! Wir sind ein Team und melden uns in der Regel innerhalb von 48 Stunden bei Ihnen. Sollten Sie keine Rückmeldung erhalten, schreiben Sie uns bitte erneut – manchmal geht auch digital etwas verloren.',
        ]);
    }

    private function getFormErrors(FormInterface $form): array
    {
        $errors = [];

        foreach ($form->getErrors() as $error) {
            $errors['global'][] = $error->getMessage();
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                foreach ($child->getErrors() as $error) {
                    $errors[$child->getName()][] = $error->getMessage();
                }
            }
        }

        return $errors;
    }
}
