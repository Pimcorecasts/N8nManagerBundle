<?php

namespace Pimcorecasts\Bundle\N8nManager\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Pimcore\Bundle\AdminBundle\Security\ContentSecurityPolicyHandler;
use Pimcorecasts\Bundle\N8nManager\Service\N8nService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/n8n-manager', name: 'n8n-manager-')]
class N8nManagerController extends AbstractN8nManagerController
{


    /**
     * @throws GuzzleException
     */
    #[Route('/', name: 'index')]
    public function indexAction(Request $request, N8nService $n8nService, ContentSecurityPolicyHandler $contentSecurityPolicyHandler): Response
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
                    $webhookPaths[] = $n8nService->getWebhookNodeArray($node);
                } elseif ($node->type == 'n8n-nodes-base.scheduleTrigger') {
                    $schedule = array_merge($schedule, $n8nService->getScheduleNodeArray($node));
                }
            }

            $lastExecution = $n8nService->getAllExecutions(limit: 1, workflowId: $workflow->id);

            $workflows[$workflow->id] = [
                'id' => $workflow->id,
                'name' => $workflow->name,
                'active' => $workflow->active,
                'createdAt' => $workflow->createdAt,
                'updatedAt' => $workflow->updatedAt,
                'nodes' => $workflow->nodes,
                'tags' => $tagNames,
                'webhookPaths' => $webhookPaths,
                'schedule' => $schedule,
                'lastExecution' => $lastExecution
            ];

        }

        // sort workflows by name (initial)
        if(!empty($workflows)) {
            uasort($workflows, function ($a, $b) {
                return $a['name'] <=> $b['name'];
            });
        }

        // Filter workflows by tag
        if ($tag = $request->get('tag')) {
            $workflows = array_filter($workflows, function ($workflow) use ($tag) {
                return in_array($tag, $workflow['tags']);
            });
        }

        return $this->render('@N8nManager/n8n-manager/index.html.twig', [
            'data' => $json->data,
            'workflows' => $workflows,
            'tags' => $tags
        ]);
    }

    /**
     * @throws GuzzleException
     */
    #[Route('/activate-workflow/{id}', name: 'activate-workflow')]
    public function activateWorkflow(Request $request, N8nService $n8nService): Response
    {
        $response = $n8nService->activateWorkflow($request->get('id'));
        return $this->json($response);
    }


    /**
     * @throws GuzzleException
     */
    #[Route('/start-webhook/{id}', name: 'start-webhook')]
    public function startWebhook(Request $request, N8nService $n8nService): Response
    {
        $workflow = $n8nService->getWorkflow($request->get('id'));
        $webhookNodeData = null;
        foreach ($workflow->nodes as $node) {
            if ($node->type == 'n8n-nodes-base.webhook') {
                $webhookNodeData = $n8nService->getWebhookNodeArray($node);
                break;
            }
        }
        if ($webhookNodeData === null) {
            return $this->json(['error' => 'No webhook node found']);
        }else{
            $client = new Client([
                'headers' => [
                    'accept' => 'application/json',
                    'X-Api-Key' => $n8nService->webhookKey
                ]
            ]);

            $webhookUrl = $webhookNodeData['fullPath'];
            if(!$workflow->active){
                $webhookUrl = $webhookNodeData['testPath'];
            }

            $promise = $client->getAsync($webhookUrl);
            $promise->wait(true);
        }

        return $this->redirectToRoute('n8n-manager-index');
    }


}
