<?php
namespace Pimcorecasts\Bundle\N8nManager\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/n8n-manager', name:'n8n-manager')]
class N8nManagerController extends AbstractN8nManagerController{

    /**
     * @throws GuzzleException
     */
    #[Route('/', name: '-index')]
    public function indexAction( Client $client ): Response
    {

        $response = $client->get( $_ENV['N8N_HOST'] . '/api/v1/workflows', [
            RequestOptions::HEADERS => [
                'accept' => 'application/json',
                'X-N8N-API-KEY' => $_ENV['N8N_API_KEY']
            ]
        ]);
        $json = json_decode($response->getBody()->getContents());

        return $this->render('n8n-manager/index.html.twig', [
            'data' => $json->data
        ]);
    }

}