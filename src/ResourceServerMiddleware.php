<?php

declare(strict_types=1);

namespace PhpRestfulapi\ResourceServerMiddleware;

use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ResourceServerMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResourceServer $server,
        private readonly ResponseFactoryInterface $responseFactory
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $request = $this->server->validateAuthenticatedRequest($request);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($this->responseFactory->createResponse());
        } catch (Throwable $exception) {
            return (new OAuthServerException(
                $exception->getMessage(),
                0,
                'unknown_error',
                500
            ))->generateHttpResponse($this->responseFactory->createResponse());
        }

        return $handler->handle($request);
    }
}
