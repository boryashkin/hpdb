<?php
namespace app\actions\web;

use app\abstracts\BaseAction;
use app\modules\web\ProfileRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Index extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getQueryParams();
        $query = isset($params['query']) && \is_string($params['query']) ? $params['query'] : null;
        $page = isset($params['page']) && \is_numeric($params['page']) ? (int)$params['page'] : 0;

        $repo = new ProfileRepository();
        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode($repo->getList($query, $page)));

        return $response;
    }
}
