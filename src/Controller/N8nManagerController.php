<?php

namespace Pimcorecasts\Bundle\N8nManager\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Pimcore\Bundle\AdminBundle\Security\ContentSecurityPolicyHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/n8n-manager', name: 'n8n-manager-')]
class N8nManagerController extends AbstractN8nManagerController
{

    /**
     * @throws GuzzleException
     */
    #[Route('/', name: 'index')]
    public function indexAction(Client $client, ContentSecurityPolicyHandler $contentSecurityPolicyHandler): Response
    {
        $contentSecurityPolicyHandler->addAllowedUrls(ContentSecurityPolicyHandler::SCRIPT_OPT, [
            'https://cdn.jsdelivr.net/'
        ]);
        $contentSecurityPolicyHandler->addAllowedUrls(ContentSecurityPolicyHandler::STYLE_OPT, [
            'https://cdn.jsdelivr.net/'
        ]);

        $response = $client->get($_ENV['N8N_HOST'] . '/api/v1/workflows', [
            RequestOptions::HEADERS => [
                'accept' => 'application/json',
                'X-N8N-API-KEY' => $_ENV['N8N_API_KEY']
            ]
        ]);
        $json = json_decode($response->getBody()->getContents());

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
                    $webhookPaths[] = [
                        'id' => $node->id,
                        'name' => $node->name,
                        'path' => $node->parameters->path,
                        'fullPath' => $_ENV['N8N_HOST'] . '/webhook/' . $node->parameters->path,
                        'testPath' => $_ENV['N8N_HOST'] . '/webhook-test/' . $node->parameters->path,
                    ];
                } elseif ($node->type == 'n8n-nodes-base.scheduleTrigger') {
                    foreach ($node->parameters->rule->interval as $scheduleItem) {
                        $field = 'days';
                        if (isset($scheduleItem->field)) {
                            $field = $scheduleItem->field;
                        }

                        if ($field == 'cronExpression') {
                            $schedule[] = [
                                'type' => 'CRON',
                                'value' => $scheduleItem->expression
                            ];
                        } else {
                            $string = json_encode($scheduleItem);
                            if (!str_contains($string, '={{')) {
                                if ($field == 'seconds') {
                                    /**
                                     * secondsInterval: 30
                                     */
                                    $schedule[] = [
                                        'type' => 'EVERY - ' . $field,
                                        'value' => $scheduleItem->secondsInterval ?? 30
                                    ];
                                } elseif ($field == 'minutes') {
                                    /**
                                     * minutesInterval: 5
                                     */
                                    $schedule[] = [
                                        'type' => 'EVERY - ' . $field,
                                        'value' => $scheduleItem->minutesInterval ?? 5
                                    ];
                                } elseif ($field == 'hours') {
                                    /**
                                     * hoursInterval: 1
                                     * triggerAtMinute: 0
                                     */
                                    $schedule[] = [
                                        'type' => 'EVERY - ' . $field,
                                        'value' => ($scheduleItem->hoursInterval ?? 1) . 'h:' . ($scheduleItem->triggerAtMinute ?? 0) . 'm'
                                    ];
                                } elseif ($field == 'days') {
                                    /**
                                     * daysInterval: 1
                                     * triggerAtHour: 0
                                     * triggerAtMinute: 0
                                     */
                                    $schedule[] = [
                                        'type' => 'EVERY - ' . $field,
                                        'value' => ($scheduleItem->daysInterval ?? 1) . 'd:' . ($scheduleItem->triggerAtHour ?? 0) . 'h:' . ($scheduleItem->triggerAtMinute ?? 0) . 'm'
                                    ];
                                } elseif ($field == 'weeks') {
                                    /**
                                     * weeksInterval: 1
                                     * triggerAtDay: 0
                                     * triggerAtHour: 0
                                     * triggerAtMinute: 0
                                     */
                                    $schedule[] = [
                                        'type' => 'EVERY - ' . $field,
                                        'value' => ($scheduleItem->weeksInterval ?? 1) . 'w:' . ($scheduleItem->triggerDay ?? 0) . 'd:' . ($scheduleItem->triggerAtHour ?? 0) . 'h:' . ($scheduleItem->triggerAtMinute ?? 0) . 'm'
                                    ];
                                } elseif ($field == 'months') {
                                    /**
                                     * monthsInterval: 1
                                     * triggerAtDayOfMonth: 1
                                     * triggerAtHour: 0
                                     * triggerAtMinute: 0
                                     */
                                    $schedule[] = [
                                        'type' => 'EVERY - ' . $field,
                                        'value' => ($scheduleItem->monthsInterval ?? 1) . 'm:' . ($scheduleItem->triggerAtDayOfMonth ?? 1) . 'd:' . ($scheduleItem->triggerAtHour ?? 0) . 'h:' . ($scheduleItem->triggerAtMinute ?? 0) . 'm'
                                    ];
                                }
                            } else {
                                $schedule[] = [
                                    'type' => 'Scheduler: ',
                                    'value' => 'used n8n Expressions, see n8n'
                                ];
                            }
                        }
                    }
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

    #[Route('/start-workflow/{id}', name: 'start-workflow')]
    public function startWorkflow(Response $response): Response
    {

        return $response;
    }

}
