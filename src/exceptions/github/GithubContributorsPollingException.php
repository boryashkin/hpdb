<?php

declare(strict_types=1);

namespace app\exceptions;

/**
 * If a request got an empty response and 202 status to repeat the request later
 */
class GithubContributorsPollingException extends \Exception
{

}
