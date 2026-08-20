<?php

namespace App\Controller;

use App\Entity\ChatConversation;
use App\Repository\ChatConversationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/chat')]
class ChatAdminController extends AbstractController
{
    #[Route('', name: 'admin_chat_index', methods: ['GET'])]
    #[Route('/', name: 'admin_chat_index_slash', methods: ['GET'])]
    public function index(ChatConversationRepository $repository): Response
    {
        return $this->render('admin/chat/index.html.twig', [
            'conversations' => $repository->findForAdminList(),
            'submittedCount' => $repository->countByStatus(ChatConversation::STATUS_SUBMITTED),
            'activeCount' => $repository->countOpenConversations(),
            'practices' => [],
        ]);
    }

    #[Route('/{id}', name: 'admin_chat_show', methods: ['GET'])]
    public function show(ChatConversation $conversation): Response
    {
        return $this->render('admin/chat/show.html.twig', [
            'conversation' => $conversation,
            'qualification' => $conversation->getQualification(),
            'practices' => [],
        ]);
    }
}
