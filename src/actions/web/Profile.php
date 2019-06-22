<?php
namespace app\actions\web;

use app\abstracts\BaseAction;
use app\models\WebsiteContent;
use app\modules\web\ProfileRepository;
use Guzzle\Common\Exception\InvalidArgumentException;
use Guzzle\Http\Url;
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
            $parsedUrl = Url::factory($profile['homepage']);
            $parsedUrl = $parsedUrl->getHost();
        } catch (InvalidArgumentException $e) {
            $parsedUrl = $profile['homepage'];
        }

        return $this->getView()->render($response, 'web/profile.html', [
            'url' => '/proxy/' . $profile->profile_id . '/',
            'sourceUrl' => $parsedUrl,
            'title' => $content->title ?? $parsedUrl,
            'metaDescription' => $parsedUrl . ' ',
            'description' => $content->description ?: 'Нет описания',
        ]);
    }
}
