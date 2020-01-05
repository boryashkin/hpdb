<?php
namespace app\actions\web;

use app\abstracts\BaseAction;
use app\exceptions\InvalidUrlException;
use app\models\WebsiteContent;
use app\modules\web\ProfileRepository;
use app\valueObjects\Url;
use MongoDB\BSON\ObjectId;
use MongoDB\Exception\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\NotFoundException;

class Profile extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $profileId = $request->getAttribute('id');
        if ($redirectResponse = $this->handleLegacyRequest($request, $response)) {
            return $redirectResponse;
        }
        if (!\is_string($profileId)) {
            throw new NotFoundException($request, $response);
        }
        try {
            $id = new ObjectId($profileId);
        } catch (InvalidArgumentException $e) {
            throw new NotFoundException($request, $response);
        }
        $repo = new ProfileRepository($this->getContainer()->get(CONTAINER_CONFIG_MONGO));
        $profile = $repo->getOneById($id);
        if (!$profile) {
            throw new NotFoundException($request, $response);
        }
        $content = WebsiteContent::query()->where('website_id', '=', new ObjectId($profile->_id))->first();
        try {
            $parsedUrl = new Url($profile['homepage']);
            $host = $parsedUrl->getHost();
        } catch (InvalidUrlException $e) {
            $parsedUrl = htmlspecialchars($profile['homepage']);
            $host = $parsedUrl;
        }

        return $this->getView()->render($response, 'web/profile.html', [
            'profile_id' => (string)$profile->_id,
            'url' => "/proxy/$profile->_id/",
            'sourceUrl' => $parsedUrl,
            'host' => $host,
            'title' => $content->title ?? $parsedUrl,
            'metaDescription' => $parsedUrl . ' ',
            'description' => $content->description ?: 'Нет описания',
        ]);
    }

    private function handleLegacyRequest(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ?ResponseInterface
    {
        $profileId = $request->getAttribute('id');
        if (!\is_numeric($profileId)) {
            return null;
        }

        $repo = new ProfileRepository($this->getContainer()->get(CONTAINER_CONFIG_MONGO));
        if ($profile = $repo->getOneByProfileId($profileId)) {
            $response = $response->withAddedHeader('Location', "/profile/$profile->_id");
            return $response->withStatus(301, 'Moved Permanently');
        }

        return null;
    }
}
