<?php

namespace App\Web\Actions\Web;

use App\Common\Abstracts\BaseAction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Crawler extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->getView()->render($response, 'web/crawler.html', [
            'title' => 'Hpdb-bot crawler',
            'metaDescription' => 'Hpdb bot crawls websites to find the best home pages on the internet / Паук Hpdb ходит по сайтам в поисках лучших домашних страниц',
        ]);
    }
}
