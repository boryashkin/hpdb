<?php
namespace app\commands;

use app\models\Website;
use app\models\WebsiteIndexHistory;
use Guzzle\Http\Url;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RedirectMiddleware;
use MongoDB\BSON\ObjectId;
use Proxy\Adapter\Guzzle\GuzzleAdapter;
use Proxy\Filter\RemoveEncodingFilter;
use Proxy\Proxy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @todo: make sure that Illuminate can handle long run connections
 */
class ReindexHompages extends Command
{
    private const PAGINATION_CNT = 10;
    private const CURL_SKIP_EXCEPTION_CODES = [
        CURLE_COULDNT_RESOLVE_HOST,
        CURLE_OPERATION_TIMEDOUT
    ];

    /** @var \Jenssegers\Mongodb\Connection */
    private $mongo;

    public function setMongo(\Jenssegers\Mongodb\Connection $mongo)
    {
        $this->mongo = $mongo;
    }

    /** @inheritDoc */
    protected function configure()
    {
        $this
            ->setName('service:reindex-homepages')
            ->setDescription('Go through all of the HPs and refill the info about them.')
            ->addOption('skip', null, InputOption::VALUE_OPTIONAL, 'skip first qty of websites')
        ;
    }

    /** @inheritDoc */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('skip')) {
            $i = (int)$input->getOption('skip');
        } else {
            $i = 0;
        }
        $websites = true;
        while ($websites) {
            $this->mongo->reconnect();
            $websites = Website::query()
                ->offset(self::PAGINATION_CNT * $i)
                ->take(self::PAGINATION_CNT)
                ->get()
                ->all();
            /** @var Website $website */
            foreach ($websites as $website) {
                try {
                    $this->reindex($website);
                } catch (\MongoDB\Driver\Exception\UnexpectedValueException $e) {
                    echo 'mongo| UnexpectedValueException: ' . $website->homepage . ' ' . substr($e->getMessage(), 0, 100) . PHP_EOL;
                    unset($e);
                } catch (\MongoDB\Driver\InvalidArgumentException $e) {
                    echo 'mongo| InvalidArgumentException: ' . $website->homepage . ' ' . substr($e->getMessage(), 0, 100) . PHP_EOL;
                    unset($e);
                } catch (\Guzzle\Common\Exception\InvalidArgumentException $e) {
                    echo 'URL| InvalidArgumentException: ' . substr($e->getMessage(), 0, 100) . PHP_EOL;
                    unset($e);
                }
            }
            $i++;
        }
        // Example code
        $output->writeLn("Information is up to date.");

    }

    /**
     * Check availability http & https and set "check date".
     * @param Website $website
     * @return |null
     */
    private function reindex(Website $website)
    {
        try {
            $parsedUrl = Url::factory($website->homepage);
        } catch (\Guzzle\Common\Exception\InvalidArgumentException $e) {
            //to know where and which exactly exceptions are
            throw $e;
        }
        $historyRow = new WebsiteIndexHistory();
        $historyRow->website_id = new ObjectId($website->_id);

        $isHttp = $parsedUrl->getScheme() === 'http';
        try {
            $parsed = $this->parseWebsite($parsedUrl->setScheme('https'));
        } catch (TransferException $e) {
            if ($this->isCurlCodeNeedToLog($e)) {
                echo 'https| ' . $website->homepage . ' ' . $e->getMessage() . PHP_EOL;
            }
            unset($e);
            try {
                $parsed = $this->parseWebsite($parsedUrl->setScheme('http'));
            } catch (TransferException $e) {
                if ($this->isCurlCodeNeedToLog($e)) {
                    echo 'http| ' . $website->homepage . ' ' . $e->getMessage() . PHP_EOL;
                }
                unset($e);

                $parsed = null;
            }
        }
        if (!$parsed) {
            $historyRow->available = false;
            $historyRow->save();

            return null;
        }
        $historyRow->available = true;
        if ($isHttp && $historyRow->is_http_only !== true) {
            $website->homepage = \str_replace('http://', 'https://', $website->homepage);
            $website->save();
        }
        if ($website->wasChanged()) {
            $website->save();
        }
        $historyRow->http_status = $parsed['httpStatus'];
        $historyRow->http_headers = $parsed['httpHeaders'];
        $historyRow->content = $parsed['content'];
        $historyRow->redirects = $parsed['redirects'];
        $historyRow->time = $parsed['time'];
        $historyRow->save();

        return null;
    }

    /**
     * @param Url $originUrl
     * @return array
     * @throws TransferException
     */
    private function parseWebsite(Url $originUrl)
    {
        $rsp = [
            'redirects' => null,
            'content' => null,
            'httpStatus' => null,
            'httpHeaders' => null,
            'time' => null,
        ];

        $stack = HandlerStack::create();
        $guzzle = new Client([
            'handler' => $stack,
            'allow_redirects' => \array_merge(RedirectMiddleware::$defaultSettings, ['track_redirects' => true]),
            'connect_timeout' => 5,
        ]);
        $proxy = new Proxy(new GuzzleAdapter($guzzle));
        $proxy->filter(new RemoveEncodingFilter());
        $clone = new Request('GET', '/', ['User-Agent' => HPDB_CRAWLER_NAME]);
        try {
            $time = microtime(true);
            $res = $proxy->forward($clone)->to((string)$originUrl);
        } catch (TransferException $e) {
            throw $e;
        }
        $rsp['time'] = microtime(true) - $time;
        if ($redirects = $res->getHeaderLine('X-Guzzle-Redirect-History')) {
            $redirects = explode(', ', $redirects);
            $rsp['redirects'] = $redirects;
        }
        $rsp['httpStatus'] = $res->getStatusCode();
        $rsp['content'] = $res->getBody()->getContents();
        $rsp['httpHeaders'] = $res->getHeaders();

        return $rsp;
    }

    /**
     * @param $code
     * @return bool
     */
    private function isCurlCodeNeedToLog(\Exception $e)
    {
        $toLog = true;
        foreach (self::CURL_SKIP_EXCEPTION_CODES as $code) {
            if (stripos($e->getMessage(), "cURL error $code:") === 0) {
                $toLog = false;
                break;
            }
        }

        return $toLog;
    }
}
