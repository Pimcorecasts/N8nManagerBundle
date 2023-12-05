<?php

namespace Pimcorecasts\Bundle\N8nManager\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Pimcore\Bundle\AdminBundle\Security\ContentSecurityPolicyHandler;
use Pimcorecasts\Bundle\N8nManager\Service\N8nService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/n8n-manager', name: 'n8n-manager-')]
class N8nManagerController extends AbstractN8nManagerController
{

    #[Route('/', name: 'index')]
    public function indexAction(N8nService $n8nService, ContentSecurityPolicyHandler $contentSecurityPolicyHandler): Response
    {
        $contentSecurityPolicyHandler->addAllowedUrls(ContentSecurityPolicyHandler::SCRIPT_OPT, [
            'https://cdn.jsdelivr.net/'
        ]);
        $contentSecurityPolicyHandler->addAllowedUrls(ContentSecurityPolicyHandler::STYLE_OPT, [
            'https://cdn.jsdelivr.net/'
        ]);

        $json = $n8nService->getWorkflowData();

        $tags = [];
        $workflows = [];
        foreach ($json->data as $workflow) {

            $tagNames = [];

            foreach ($workflow->tags as $tag) {
                $tags[] = $tag;
                $tagNames[] = $tag->name;
            }

            // Webhooks
            $webhookPaths = [];
            $schedule = [];
            foreach ($workflow->nodes as $node) {
                if ($node->type == 'n8n-nodes-base.webhook') {
                    $webhookPaths[] = $n8nService->getWebhookNodeArray($node));
                } elseif ($node->type == 'n8n-nodes-base.scheduleTrigger') {
                    $schedule = array_merge($schedule, $n8nService->getScheduleNodeArray($node));
                }
            }


            $workflows[] = [
                'id' => $workflow->id,
                'name' => $workflow->name,
                'active' => $workflow->active,
                'createdAt' => $workflow->createdAt,
                'updatedAt' => $workflow->updatedAt,
                'nodes' => $workflow->nodes,
                'tags' => $tagNames,
                'webhookPaths' => $webhookPaths,
                'schedule' => $schedule
            ];
            //p_r($webhookPaths);


        }

        return $this->render('@N8nManager/n8n-manager/index.html.twig', [
            'data' => $json->data,
            'workflows' => $workflows,
            'tags' => $tags
        ]);
    }

    #[Route('/activate-workflow/{id}', name: 'activate-workflow')]
    public function activateWorkflow(Request $request, N8nService $n8nService): Response
    {
        $response = $n8nService->activateWorkflow($request->get('id'));
        return $this->json($response);
    }

}
