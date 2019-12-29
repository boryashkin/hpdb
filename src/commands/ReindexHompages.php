<?php
namespace app\commands;

use app\models\Website;
use app\services\website\WebsiteIndexer;
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
    /** @var WebsiteIndexer */
    private $websiteIndexer;

    public function setMongo(\Jenssegers\Mongodb\Connection $mongo)
    {
        $this->mongo = $mongo;
    }

    public function setWebsiteIndexer(WebsiteIndexer $indexer)
    {
        $this->websiteIndexer = $indexer;
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
        $cnt = Website::query()->count() / self::PAGINATION_CNT;
        while ($websites && $i <= $cnt) {
            $this->mongo->reconnect();
            $websites = Website::query()
                ->offset($i * self::PAGINATION_CNT)
                ->take(self::PAGINATION_CNT)
                ->get()
                ->all();
            /** @var Website $website */
            foreach ($websites as $website) {
                $this->reindex($website);
            }
            $i++;
        }
        // Example code
        $output->writeLn("Information is up to date.");

    }

    /**
     * Check availability http & https and set "check date".
     * @param Website $website
     * @return void
     */
    public function reindex(Website $website): void
    {
        try {
            $result = $this->websiteIndexer->reindex($website);
            if ($website->isDirty()) {
                $website->save();
            }
            if ($result->historyRow) {
                $result->historyRow->save();
            }
        } catch (\MongoDB\Driver\Exception\UnexpectedValueException $e) {
            echo 'mongo| UnexpectedValueException: ' . $website->homepage . ' ' . substr($e->getMessage(), 0, 100) . PHP_EOL;
            unset($e);
        } catch (\MongoDB\Driver\Exception\InvalidArgumentException $e) {
            echo 'mongo| InvalidArgumentException: ' . $website->homepage . ' ' . substr($e->getMessage(), 0, 100) . PHP_EOL;
            unset($e);
        } catch (\Guzzle\Common\Exception\InvalidArgumentException $e) {
            echo 'URL| InvalidArgumentException: ' . substr($e->getMessage(), 0, 100) . PHP_EOL;
            unset($e);
        }
        if ($result && $result->errors) {
            foreach ($result->errors as $arr) {
                $msg = $arr[\key($arr)];
                if ($this->isCurlCodeNeedToLog($msg)) {
                    echo 'error | http' . ($website->isHttps() ? 's' : '') . ' | ' . $msg;
                }
            }
        }
    }

    /**
     * @param string $errorMessage
     * @return bool
     */
    private function isCurlCodeNeedToLog($errorMessage)
    {
        $toLog = true;
        foreach (self::CURL_SKIP_EXCEPTION_CODES as $code) {
            if (stripos($errorMessage, "cURL error $code:") === 0) {
                $toLog = false;
                break;
            }
        }

        return $toLog;
    }
}
