<?php

namespace App\Web\Api\V1\Profile\Actions;

use App\Common\Abstracts\BaseAction;
use App\Common\Exceptions\RepositoryFilterException;
use App\Common\Repositories\Filters\WebsiteFilter;
use App\Common\Repositories\ProfileRepository;
use App\Web\Api\V1\Profile\Builders\WebsiteLightResponseBuilder;
use App\Web\Api\V1\Profile\Builders\WebsiteResponseBuilder;
use MongoDB\BSON\ObjectId;
use MongoDB\Exception\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\SlimException;

class Index extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getQueryParams();
        $query = isset($params['query']) && \is_string($params['query']) ? $params['query'] : null;
        $fromId = isset($params['fromId']) && \is_string($params['fromId']) ? (string)$params['fromId'] : 0;
        $group = isset($params['group']) && \is_string($params['group']) ? $params['group'] : null;
        $sort = isset($params['sort']) && \is_string($params['sort']) ? $params['sort'] : null;
        $limit = isset($params['limit']) && \is_numeric($params['limit']) ? $params['limit'] : 30;

        try {
            if ($group) {
                $group = new ObjectId($group);
            } else {
                $group = null;
            }
            if ($fromId) {
                $fromId = new ObjectId($fromId);
            } else {
                $fromId = null;
            }
        } catch (InvalidArgumentException | \Exception $e) {
            $group = null;
        }

        $filter = new WebsiteFilter();
        try {
            $filter->homepageLike = $query;
            $filter->group = $group;
            $filter->setLimit($limit);
            $filter->fromId = $fromId;
            if ($sort) {
                $filter->setSortDirection($sort);
            }
        } catch (RepositoryFilterException $exception) {
            $errors = ['error' => $exception->getMessage()];
            $response->getBody()->write(json_encode($errors));
            $response = $response->withStatus(400);

            throw new SlimException($request, $response);
        }

        if ($query) {
            $responseBuilder = new WebsiteLightResponseBuilder();
        } else {
            $responseBuilder = new WebsiteResponseBuilder();
        }
        $repo = new ProfileRepository($this->getContainer()->get(CONTAINER_CONFIG_MONGO));
        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()
            ->write(\json_encode($responseBuilder->createList($repo->find($filter))));

        return $response;
    }
}
