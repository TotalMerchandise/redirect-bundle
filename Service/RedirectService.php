<?php

namespace Autologic\Bundle\RedirectBundle\Service;

use Autologic\Bundle\RedirectBundle\Exception\RedirectionRuleNotFoundException;
use Autologic\Bundle\RedirectBundle\ValueObject\Redirect;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class RedirectService
{
    /**
     * @var array
     */
    private $rules;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->rules = $container->getParameter('autologic_redirect.rules');
    }

    /**
     * @param Request $request
     *
     * @throws RedirectionRuleNotFoundException
     *
     * @return RedirectResponse
     */
    public function redirect(Request $request)
    {
        $url = $request->getRequestUri();

        foreach ($this->rules as $redirect) {
            $redirect = (new Redirect())->fromConfigRule($redirect)->withRequest($request);

            if (strstr($url, $redirect->getPattern())) {
                return new RedirectResponse(
                    $redirect->getProtocol() . $redirect->getRedirectURI() . $redirect->getPath() . '?' . $request->getQueryString(),
                    $redirect->getStatusCode()
                );
            }
        }

        throw new RedirectionRuleNotFoundException('Redirection rule not found for '.$url);
    }
}
