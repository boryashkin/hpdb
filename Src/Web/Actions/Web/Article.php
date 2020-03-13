<?php

namespace App\Web\Actions\Web;

use App\Common\Abstracts\BaseAction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Article extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->getView()->render($response, 'web/article/createwebsite.html', [
            'title' => 'Запусти свой сайт',
            'metaDescription' => 'Сделай себе сайт/домашнюю страницу. Это будет служить резюме, площадкой для размещения своего творчества, в том числе технического. Рассажи о себе.',
        ]);
    }
}
