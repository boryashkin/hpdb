<?php

namespace app\actions\api\v1\group;

use app\abstracts\BaseAction;
use app\models\WebsiteGroup;
use MongoDB\BSON\ObjectId;
use MongoDB\Exception\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\NotFoundException;
use Slim\Exception\SlimException;

class Delete extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $id = $request->getAttribute('id', null);

        try {
            $id = new ObjectId($id);
        } catch (InvalidArgumentException $e) {
            throw new SlimException($request, $response);
        }
        $this->getContainer()->get(CONTAINER_CONFIG_MONGO);
        $websiteGroup = WebsiteGroup::query()->find($id);
        if (!$websiteGroup) {
            throw new NotFoundException($request, $response);
        }
        $websiteGroup->is_deleted = true;
        $websiteGroup->save();

        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode($websiteGroup));

        return $response;
    }
}
