<?php

declare(strict_types=1);

namespace App\Web\Actions\Admin\Website\Categorisation;

use App\Common\Abstracts\BaseAction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Index extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->getView()->render($response, 'admin/website/categorisation/index.html', [
            'title' => 'Соотнесение сайта категории',
        ]);
    }
}
