<?php
declare(strict_types=1);

namespace NetIdea\WebBase\Controller;

use NetIdea\WebBase\Service\FormContactService;
use NetIdea\WebBase\Service\NavigationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractBaseController
{
    public function __construct(
        private readonly NavigationService $navigation,
        private readonly FormContactService $formContactService,
    ) {
    }

    #[Route(path: '/kontakt', name: 'app_contact', methods: ['GET'])]
    public function contact(): Response
    {
        // Get form for rendering
        $form = $this->formContactService->getForm();

        return $this->render('pages/kontakt.html.twig', [
            'slug'     => 'kontakt',
            'navItems' => $this->navigation->getItems(),
            'form'     => $form->createView(),
        ]);
    }

    #[Route(path: '/api/contact', name: 'api_contact', methods: ['POST'])]
    public function contactApi(): Response
    {
        return $this->formContactService->handleAjax();
    }
}
