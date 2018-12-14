<?php
namespace app\actions\web;

use app\abstracts\BaseAction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Profile extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->getView()->render($response, 'web/profile.html', [
            'url' => 'https://borisd.ru',
            'title' => 'Десятский Борис',
            'description' => 'Борющийся с ленью',
        ]);
    }
}
