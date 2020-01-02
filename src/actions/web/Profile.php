<?php
namespace app\actions\web;

use app\abstracts\BaseAction;
use app\exceptions\InvalidUrlException;
use app\models\WebsiteContent;
use app\modules\web\ProfileRepository;
use app\valueObjects\Url;
use MongoDB\BSON\ObjectId;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\NotFoundException;

class Profile extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $profileId = $request->getAttribute('id');
        $repo = new ProfileRepository($this->getContainer()->get(CONTAINER_CONFIG_MONGO));
        $profile = $repo->getOne($profileId);
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
            'profile_id' => $profileId,
            'url' => '/proxy/' . $profile->profile_id . '/',
            'sourceUrl' => $parsedUrl,
            'host' => $host,
            'title' => $content->title ?? $parsedUrl,
            'metaDescription' => $parsedUrl . ' ',
            'description' => $content->description ?: 'Нет описания',
        ]);
    }
}
