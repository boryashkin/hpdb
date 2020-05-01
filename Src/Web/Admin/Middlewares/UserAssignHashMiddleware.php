<?php

declare(strict_types=1);

namespace App\Web\Admin\Middlewares;

use App\Common\Abstracts\BaseMiddleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UserAssignHashMiddleware extends BaseMiddleware
{
    public const USER_HASH_HEADER = 'USER-HASH';

    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $userAgent = implode(';', $request->getHeader('User-Agent'));
        $userHash = crc32($_SERVER['REMOTE_ADDR'] . $userAgent);
        $response = $response->withAddedHeader(self::USER_HASH_HEADER, $userHash);

        $response = parent::__invoke($request, $response, $next);

        return $response;
    }
}
