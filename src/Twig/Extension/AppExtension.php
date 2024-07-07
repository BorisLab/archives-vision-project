<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\AppExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\HttpFoundation\RequestStack;

class AppExtension extends AbstractExtension
{
    private $requestStack;

    public function __construct(RequestStack $requestStack) {
        $this->requestStack = $requestStack;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('set_active_route', [$this, 'setActiveRoute']),
        ];
    }

    public function setActiveRoute(array $routes, ?string $activeClass = 'active') : string {
        $currentRoute = $this->requestStack->getCurrentRequest()->attributes->get('_route');
        
        return in_array($currentRoute, $routes, true) == $routes ? $activeClass : '';
    }
}
