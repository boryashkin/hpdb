<?php
namespace app\actions\web;

use app\abstracts\BaseAction;
use app\modules\web\ProfileRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\NotFoundException;

class Profile extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $profileId = $request->getAttribute('id');
        $repo = new ProfileRepository();
        $profile = $repo->getOne($profileId);
        if (!$profile) {
            throw new NotFoundException($request, $response);
        }

        return $this->getView()->render($response, 'web/profile.html', [
            'url' => '/proxy/' . $profile['profile_id'] . '/',
            'title' => $profile['homepage'],
            'description' => 'Нет описания',
        ]);
    }
}
