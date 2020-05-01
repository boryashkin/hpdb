<?php

declare(strict_types=1);

namespace App\Web\Common\Middlewares\Security;

use App\Common\Abstracts\BaseMiddleware;
use App\Common\Services\IpCheckService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\StatusCode;

class IpPassFilterMiddleware extends BaseMiddleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        if (!$this->getIpCheckerService()->checkIp($_SERVER['REMOTE_ADDR'])) {
            $response = $response->withStatus(StatusCode::HTTP_FORBIDDEN);

            return $response;
        }
        $response = parent::__invoke($request, $response, $next);


        return $response;
    }

    private function getIpCheckerService(): IpCheckService
    {
        return $this->getContainer()->get(IpCheckService::class);
    }
}
