<?php

namespace Pimcorecasts\Bundle\N8nManager\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/n8n', name: 'n8n-workflow-')]
class N8nWorkflowController extends AbstractN8nManagerController
{

    #[Route('/cmd', name: 'command')]
    public function startAction(Response $response): Response
    {

        $process = new Process(['php', 'bin/console pimcore:cache:clear']);
        $process->run();
        if (!$process->isSuccessful()) {
            echo 'Sorry';
        }else{
            echo 'Done';
        }
        echo $process->getOutput();

        return $response;
    }

}
