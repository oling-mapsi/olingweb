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
        $conversations = $repository->findForAdminList();

        return $this->render('admin/chat/index.html.twig', [
            'conversations' => $conversations,
            'submittedCount' => $repository->countByStatus(ChatConversation::STATUS_SUBMITTED),
            'activeCount' => $repository->countOpenConversations(),
            'dashboard' => $this->buildDashboard($conversations),
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

    /**
     * @param ChatConversation[] $conversations
     * @return array<string, mixed>
     */
    private function buildDashboard(array $conversations): array
    {
        $metrics = [
            'total' => count($conversations),
            'started' => 0,
            'needs' => 0,
            'qualified_opportunities' => 0,
            'contact_offers' => 0,
            'note_offers' => 0,
            'diagnostics' => 0,
            'notes' => 0,
            'lead_pending' => 0,
            'leads' => 0,
            'avg_exchanges_before_contact_offer' => null,
            'llm_unavailable' => 0,
            'avg_latency_ms' => null,
        ];
        $domains = [];
        $latencies = [];
        $exchangesBeforeContact = [];

        foreach ($conversations as $conversation) {
            $qualification = $conversation->getQualification();
            $need = $qualification['primary_need'] ?? 'unknown';
            if ($need !== 'unknown' && $need !== null && $need !== '') {
                ++$metrics['needs'];
            }
            if (($qualification['commercial_stage'] ?? null) === 'qualified_opportunity') {
                ++$metrics['qualified_opportunities'];
            }
            $domains[$need ?: 'unknown'] = ($domains[$need ?: 'unknown'] ?? 0) + 1;

            if ($conversation->getStatus() === ChatConversation::STATUS_LEAD_PENDING) {
                ++$metrics['lead_pending'];
            }
            if ($conversation->getStatus() === ChatConversation::STATUS_SUBMITTED) {
                ++$metrics['leads'];
            }

            $visitorCount = 0;
            $firstContactOfferCaptured = false;
            foreach ($conversation->getMessages() as $message) {
                if ($message->getRole() === 'visitor') {
                    ++$visitorCount;
                    $metrics['started'] = $metrics['started'] + 1;
                    continue;
                }
                if (in_array($message->getMessageType(), ['contact_offer', 'lead_request'], true)) {
                    ++$metrics['contact_offers'];
                    if (!$firstContactOfferCaptured) {
                        $exchangesBeforeContact[] = $visitorCount;
                        $firstContactOfferCaptured = true;
                    }
                }
                if (str_contains(mb_strtolower((string) $message->getContent()), 'note de cadrage')) {
                    ++$metrics['note_offers'];
                }
                if ($message->getMessageType() === 'diagnostic') {
                    ++$metrics['diagnostics'];
                }
                if ($message->getMessageType() === 'scoping_note') {
                    ++$metrics['notes'];
                }
                if ($message->getProvider() === 'llm_unavailable' || $message->getMessageType() === 'technical_unavailable') {
                    ++$metrics['llm_unavailable'];
                }
                if ($message->getLatencyMs() !== null) {
                    $latencies[] = $message->getLatencyMs();
                }
            }
        }

        $metrics['started'] = min($metrics['started'], $metrics['total']);
        $metrics['avg_latency_ms'] = $latencies === [] ? null : (int) round(array_sum($latencies) / count($latencies));
        $metrics['avg_exchanges_before_contact_offer'] = $exchangesBeforeContact === [] ? null : round(array_sum($exchangesBeforeContact) / count($exchangesBeforeContact), 1);

        arsort($domains);

        return [
            'metrics' => $metrics,
            'domains' => $domains,
            'rates' => [
                'conversation_to_lead' => $metrics['started'] > 0 ? round(($metrics['leads'] / $metrics['started']) * 100, 1) : 0,
                'diagnostic_to_lead' => $metrics['diagnostics'] > 0 ? round(($metrics['leads'] / $metrics['diagnostics']) * 100, 1) : 0,
                'note_to_lead' => $metrics['notes'] > 0 ? round(($metrics['leads'] / $metrics['notes']) * 100, 1) : 0,
                'form_to_lead' => $metrics['lead_pending'] > 0 ? round(($metrics['leads'] / ($metrics['lead_pending'] + $metrics['leads'])) * 100, 1) : 0,
                'qualified_opportunity_to_lead' => $metrics['qualified_opportunities'] > 0 ? round(($metrics['leads'] / $metrics['qualified_opportunities']) * 100, 1) : 0,
            ],
        ];
    }
}
