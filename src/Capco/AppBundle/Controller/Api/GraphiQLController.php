<?php

namespace Capco\AppBundle\Controller\Api;

use Twig\Environment as TwigEnvironment;
use Symfony\Component\HttpFoundation\Response;
use Overblog\GraphiQLBundle\Config\GraphiQLViewConfig;
use Symfony\Component\Routing\Annotation\Route;
use Overblog\GraphiQLBundle\Config\GraphiQLControllerEndpoint;

final class GraphiQLController
{
    private TwigEnvironment $twig;
    private GraphiQLViewConfig $viewConfig;
    private GraphiQLControllerEndpoint $graphQLEndpoint;

    public function __construct(
        TwigEnvironment $twig,
        GraphiQLViewConfig $viewConfig,
        GraphiQLControllerEndpoint $graphQLEndpoint
    ) {
        $this->twig = $twig;
        $this->viewConfig = $viewConfig;
        $this->graphQLEndpoint = $graphQLEndpoint;
    }

    /**
     * @Route("/graphiql", name="graphiql_endpoint", defaults={"_feature_flags" = "public_api"}, options={"i18n" = false})
     * @Route("/graphiql/{schemaName}", name="graphiql_multiple_endpoint", condition="'%kernel.environment%' === 'dev'", requirements={"schemaName" = "public|internal|dev"}, options={"i18n" = false})
     */
    public function indexAction($schemaName = null): Response
    {
        $endpoint =
            null === $schemaName
                ? $this->graphQLEndpoint->getDefault()
                : $this->graphQLEndpoint->getBySchema($schemaName);

        return Response::create(
            $this->twig->render($this->viewConfig->getTemplate(), [
                'schemaName' => $schemaName,
                'endpoint' => $endpoint,
                'versions' => [
                    'graphiql' => $this->viewConfig->getJavaScriptLibraries()->getGraphiQLVersion(),
                    'react' => $this->viewConfig->getJavaScriptLibraries()->getReactVersion(),
                    'fetch' => $this->viewConfig->getJavaScriptLibraries()->getFetchVersion(),
                ],
            ])
        );
    }
}
