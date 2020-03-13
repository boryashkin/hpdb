<?php

namespace app\actions\api\v1\group;

use app\abstracts\BaseAction;
use app\models\WebsiteGroup;
use MongoDB\Driver\Exception\ServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\SlimException;

class Create extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getParsedBody();
        $name = isset($params['name']) && \is_string($params['name']) ? $params['name'] : null;
        $slug = isset($params['slug']) && \is_string($params['slug']) ? $params['slug'] : null;
        $description = isset($params['description']) && \is_string($params['description']) ? $params['description'] : null;
        $logo = isset($params['logo']) && \is_string($params['logo']) ? $params['logo'] : null;
        if (!$name || !$slug) {
            $response = $response->withStatus(400);
            $response->getBody()->write('{"errors": ["Slug and name fields are required"]}');

            throw new SlimException($request, $response);
        }
        if (!preg_match('/[a-zA-Z0-9\-]{0,20}/', $slug, $out) || $out[0] !== $slug) {
            $response->getBody()->write('{"errors": ["Slug should be an alphanumeric (en) string with \"-\" 20 symbols max"]}');
            $response = $response->withStatus(400);

            throw new SlimException($request, $response);
        }
        $websiteGroup = new WebsiteGroup();
        $websiteGroup->name = $name;
        $websiteGroup->slug = $slug;
        $websiteGroup->description = $description;
        $websiteGroup->logo = $logo;
        $websiteGroup->show_on_main = true; //todo: turn to false, when moderation will be done
        $websiteGroup->is_deleted = false;

        $this->getContainer()->get(CONTAINER_CONFIG_MONGO);

        try {
            $websiteGroup->save();
        } catch (ServerException $e) {
            $response->getBody()->write('{"errors": ["Slug may be already exists"]}');
            $response = $response->withStatus(400);

            throw new SlimException($request, $response);
        }

        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode($websiteGroup));

        return $response;
    }
}
