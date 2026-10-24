<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class CrmAccessController extends AbstractController
{
    #[Route('/crm/login', name: 'crm_login', methods: ['GET', 'POST'])]
    public function login(Request $request, AuthenticationUtils $authentication): Response
    {
        if (!in_array($request->getHost(), ['crm.oling.fr', 'crm.localhost'], true)) { throw $this->createNotFoundException(); }
        if ($this->isGranted('ROLE_ADMIN')) { return $this->redirectToRoute('admin_crm'); }
        return $this->render('admin/crm/login.html.twig', [
            'last_username' => $authentication->getLastUsername(),
            'error' => $authentication->getLastAuthenticationError(),
        ]);
    }

    #[Route('/crm/logout', name: 'crm_logout', methods: ['POST'])]
    public function logout(): never { throw new \LogicException('Handled by the CRM firewall.'); }
}
