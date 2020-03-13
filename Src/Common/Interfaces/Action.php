<?php

namespace App\Common\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Action
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;
}
