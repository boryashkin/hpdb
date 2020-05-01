<?php

namespace App\Web\Api\V1\Profile\Actions;

use App\Common\Abstracts\BaseAction;
use App\Common\Repositories\ProfileRepository;
use App\Web\Api\V1\Profile\Builders\WebsiteLightResponseBuilder;
use MongoDB\BSON\ObjectId;
use MongoDB\Exception\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Index extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getQueryParams();
        $query = isset($params['query']) && \is_string($params['query']) ? $params['query'] : null;
        $page = isset($params['page']) && \is_numeric($params['page']) ? (int)$params['page'] : 0;
        $group = isset($params['group']) && \is_string($params['group']) ? $params['group'] : null;
        $limit = isset($params['limit']) && \is_numeric($params['limit']) ? $params['limit'] : 30;

        try {
            if ($group) {
                $group = new ObjectId($group);
            } else {
                $group = null;
            }
        } catch (InvalidArgumentException | \Exception $e) {
            $group = null;
        }

        $responseBuilder = new WebsiteLightResponseBuilder();
        $repo = new ProfileRepository($this->getContainer()->get(CONTAINER_CONFIG_MONGO));
        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()
            ->write(\json_encode($responseBuilder->createFromArray($repo->getList($page, $query, $group, $limit))));

        return $response;
    }
}
