<?php
namespace App\Controller\Ai;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
/** V1 simple never launches an autonomous executor, regardless of legacy configuration. */
final class AiRelayController extends AbstractController
{
    #[Route('/api/ai-relay',name:'admin_ai_relay',methods:['POST'])]
    public function dispatch(): JsonResponse
    {
        return $this->json(['error'=>'autonomous_relay_unavailable_in_v1'],403);
    }
}
