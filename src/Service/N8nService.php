<?php

namespace Pimcorecasts\Bundle\N8nManager\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use stdClass;

class N8nService
{
    private string $host = '';
    private string $apiKey = '';
    public mixed $webhookKey = '';

    public function __construct(private readonly Client $client)
    {
        $this->host = $_ENV['N8N_HOST'];
        $this->apiKey = $_ENV['N8N_API_KEY'];
        $this->webhookKey = $_ENV['N8N_WEBHOOK_KEY'];
    }

    /**
     * @throws GuzzleException
     */
    public function getWorkflowData(): stdClass
    {
        $response = $this->client->get($this->host . '/api/v1/workflows', [
            RequestOptions::HEADERS => [
                'accept' => 'application/json',
                'X-N8N-API-KEY' => $this->apiKey
            ]
        ]);
        return json_decode($response->getBody()->getContents());
    }

    /**
     * @throws GuzzleException
     */
    public function getWorkflow(string $id): stdClass
    {
        $response = $this->client->get($this->host . '/api/v1/workflows/' . $id, [
            RequestOptions::HEADERS => [
                'accept' => 'application/json',
                'X-N8N-API-KEY' => $this->apiKey
            ]
        ]);
        return json_decode($response->getBody()->getContents());
    }

    /**
     * @throws GuzzleException
     */
    public function activateWorkflow(string $id): stdClass
    {
        $response = $this->client->put($this->host . '/api/v1/workflows/' . $id . '/activate', [
            RequestOptions::HEADERS => [
                'accept' => 'application/json',
                'X-N8N-API-KEY' => $this->apiKey
            ]
        ]);
        return json_decode($response->getBody()->getContents());
    }

    /**
     * @throws GuzzleException
     */
    public function deactivateWorkflow(string $id): stdClass
    {
        $response = $this->client->put($this->host . '/api/v1/workflows/' . $id . '/deactivate', [
            RequestOptions::HEADERS => [
                'accept' => 'application/json',
                'X-N8N-API-KEY' => $this->apiKey
            ]
        ]);
        return json_decode($response->getBody()->getContents());
    }

    /**
     * @throws GuzzleException
     */
    public function getAllExecutions(?int $limit = null, ?string $cursor = null, ?string $workflowId = null, ?string $status = null, bool $includeData = false): stdClass
    {
        $options = [
            RequestOptions::HEADERS => [
                'accept' => 'application/json',
                'X-N8N-API-KEY' => $this->apiKey
            ]
        ];
        $query = [];
        if ($limit !== null && $limit > 0 && $limit < 250 && $limit != 100) {
            $query['limit'] = $limit;
        }
        if ($cursor !== null) {
            $query['cursor'] = $cursor;
        }
        if ($workflowId !== null) {
            $query['workflowId'] = $workflowId;
        }
        if (in_array($status, ['error', 'success', 'waiting'])) {
            $query['status'] = $status;
        }
        if ($includeData) {
            $query['includeData'] = $includeData;
        }
        if (count($query) > 0) {
            $options[RequestOptions::QUERY] = $query;
        }
        $response = $this->client->get($this->host . '/api/v1/executions', $options);
        return json_decode($response->getBody()->getContents());
    }

    /**
     * @throws GuzzleException
     */
    public function getExecution(string $id, bool $includeData = false): stdClass
    {
        $options = [
            RequestOptions::HEADERS => [
                'accept' => 'application/json',
                'X-N8N-API-KEY' => $this->apiKey
            ]
        ];
        if ($includeData) {
            $options[RequestOptions::QUERY] = [
                'includeData' => $includeData
            ];
        }
        $response = $this->client->get($this->host . '/api/v1/executions/' . $id, $options);
        return json_decode($response->getBody()->getContents());
    }

    public function getWebhookNodeArray(stdClass $node): array
    {
        return [
            'id' => $node->id,
            'name' => $node->name,
            'path' => $node->parameters->path,
            'fullPath' => $this->host . '/webhook/' . $node->parameters->path,
            'testPath' => $this->host . '/webhook-test/' . $node->parameters->path,
        ];
    }

    public function getScheduleNodeArray(stdClass $node): array
    {
        $schedule = [];
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
                         * triggerDayOfWeek: 1
                         * triggerAtHour: 0
                         * triggerAtMinute: 0
                         */
                        $schedule[] = [
                            'type' => 'EVERY - ' . $field,
                            'value' => ($scheduleItem->weeksInterval ?? 1) . 'w:' . ($scheduleItem->triggerDayOfWeek ?? 1) . 'd:' . ($scheduleItem->triggerAtHour ?? 0) . 'h:' . ($scheduleItem->triggerAtMinute ?? 0) . 'm'
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

        return $schedule;
    }

}
