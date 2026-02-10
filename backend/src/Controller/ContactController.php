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

    #[Route(path: '/kontakt', name: 'web_base_contact', methods: ['GET'], priority: -100)]
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

    #[Route(path: '/api/contact', name: 'web_base_api_contact', methods: ['POST'], priority: -100)]
    public function contactApi(): Response
    {
        return $this->formContactService->handleAjax();
    }
}
