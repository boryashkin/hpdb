<?php

namespace App\Web\Actions\Api\V1\Group;

use App\Common\Abstracts\BaseAction;
use App\Common\Repositories\WebsiteGroupRepository;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Exception\ServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\SlimException;

class Update extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getParsedBody();
        $id = $request->getAttribute('id', null);
        $showOnMain = $params['show_on_main'] ?? null;

        try {
            $id = new ObjectId($id);
        } catch (\Exception $e) {
            $response->getBody()->write('{"errors": ["Id is not valid"]}');
            $response = $response->withStatus(400);

            throw new SlimException($request, $response);
        }
        if (!\is_bool($showOnMain)) {
            $response->getBody()->write('{"errors": ["show_on_main must be bool"]}');
            $response = $response->withStatus(400);

            throw new SlimException($request, $response);
        }
        $repo = new WebsiteGroupRepository($this->getContainer()->get(CONTAINER_CONFIG_MONGO));
        $group = $repo->getOneById($id);
        if (!$group) {
            $response->getBody()->write('{"errors": ["Not Found"]}');
            $response = $response->withStatus(404, 'Not Found');

            throw new SlimException($request, $response);
        }

        try {
            $group->update(['show_on_main' => $showOnMain]);
        } catch (ServerException $e) {
            $response->getBody()->write('{"errors": ["Slug may be already exists"]}');
            $response = $response->withStatus(400);

            throw new SlimException($request, $response);
        }

        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode($group));

        return $response;
    }
}
