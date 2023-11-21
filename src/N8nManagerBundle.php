<?php
/**
 *
 * Date: 21.10.2021
 * Time: 10:35
 *
 */
namespace Pimcorecasts\Bundle\N8nManager;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class N8nManagerBundle extends AbstractPimcoreBundle
{
    public function getJsPaths(): array
    {
        return [
        ];
    }

    public function getEditmodeJsPaths(): array
    {
        return [
        ];
    }

    public function getCssPaths(): array
    {
        return [
        ];
    }

    public function getEditmodeCssPaths(): array
    {
        return [
        ];
    }

    public function getVersion(): string
    {    
        return \Composer\InstalledVersions::getVersion('pimcorecasts/n8n-manager-bundle');
    }

    public function getDescription(): string
    {
        return 'N8n Manager Bundle';
    }
}