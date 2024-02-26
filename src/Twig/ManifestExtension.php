<?php
// src/Twig/AppExtension.php
namespace Pimcorecasts\Bundle\N8nManager\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ManifestExtension extends AbstractExtension
{
    const MANIFEST_PATH = __DIR__ . '/../Resources/public/dist/.vite/manifest.json';
    public function getFunctions(): array
    {
        return [
            new TwigFunction('manifest', [$this, 'getManifestData']),
        ];
    }

    public function getManifestData( string $entryPoint )
    {
        if( !file_exists(self::MANIFEST_PATH)) {
            return [];
        }
        $n8nManifest = file_get_contents( self::MANIFEST_PATH);
        if( !$n8nManifest ) {
            return [];
        }
        $n8nManifestData = json_decode($n8nManifest, true);
        return $n8nManifestData[$entryPoint];
    }
}