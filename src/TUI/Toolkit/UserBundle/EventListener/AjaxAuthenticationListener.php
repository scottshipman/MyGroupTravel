<?php

namespace TUI\Toolkit\UserBundle\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AjaxAuthenticationListener
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Handles security related exceptions.
     *
     * @param GetResponseForExceptionEvent $event An GetResponseForExceptionEvent instance
     */
    public function onCoreException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $request = $event->getRequest();

        if ($request->isXmlHttpRequest()) {
            if ($exception instanceof AuthenticationException || $exception instanceof AccessDeniedException) {
                // Generate the absolute URL to the login page.
                $route = $this->container->get('router')->generate('fos_user_security_login', array(), UrlGeneratorInterface::ABSOLUTE_URL);
                // Return a JSON response with the login URL.
                $event->setResponse(new JsonResponse(array(
                    'route' => $route
                ), 403));
            }
        }
    }
}
