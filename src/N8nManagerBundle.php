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
    public function getJsPaths()
    {
        return [
        ];
    }

    public function getEditmodeJsPaths()
    {
        return [
        ];
    }

    public function getCssPaths(){
        return [
        ];
    }

    public function getEditmodeCssPaths()
    {
        return [
        ];
    }

    public function getVersion()
    {    
        return \Composer\InstalledVersions::getVersion('pimcorecasts/n8n-manager-bundle');
    }

    public function getDescription()
    {
        return 'N8n Manager Bundle';
    }
}