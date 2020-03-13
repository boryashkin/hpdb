<?php

namespace App\Cli\Commands;

use App\Common\Exceptions\InvalidUrlException;
use App\Common\MessageBus\Messages\Crawlers\NewWebsiteToCrawlMessage;
use App\Common\Models\Website;
use App\Common\Services\Website\WebsiteIndexer;
use App\Common\ValueObjects\Url;
use MongoDB\BSON\ObjectId;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @todo: make sure that Illuminate can handle long run connections
 */
class ReindexHompages extends Command
{
    private const PAGINATION_CNT = 1000;
    private const CURL_SKIP_EXCEPTION_CODES = [
        CURLE_COULDNT_RESOLVE_HOST,
        CURLE_OPERATION_TIMEDOUT,
    ];

    /** @var \Jenssegers\Mongodb\Connection */
    private $mongo;
    /** @var WebsiteIndexer */
    private $websiteIndexer;
    /** @var MessageBusInterface */
    private $crawlersBus;

    public function setMongo(\Jenssegers\Mongodb\Connection $mongo)
    {
        $this->mongo = $mongo;
    }

    public function setWebsiteIndexer(WebsiteIndexer $indexer)
    {
        $this->websiteIndexer = $indexer;
    }

    public function setCrawlersBus(MessageBusInterface $bus): void
    {
        $this->crawlersBus = $bus;
    }

    public function getCrawlersBus(): MessageBusInterface
    {
        return $this->crawlersBus;
    }

    /**
     * Check availability http & https and set "check date".
     */
    public function reindex(Website $website): void
    {
        try {
            $url = new Url($website->homepage);
        } catch (InvalidUrlException | \TypeError $e) {
            return;
        }
        $message = new NewWebsiteToCrawlMessage(new ObjectId($website->_id), $url);
        $this->getCrawlersBus()->dispatch($message);
    }

    /** {@inheritdoc} */
    protected function configure()
    {
        $this
            ->setName('service:reindex-homepages')
            ->setDescription('Go through all of the HPs and refill the info about them.')
            ->addOption('skip', null, InputOption::VALUE_OPTIONAL, 'skip first qty of websites');
    }

    /** {@inheritdoc} */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('skip')) {
            $i = (int)$input->getOption('skip');
        } else {
            $i = 0;
        }
        $websites = true;
        $cnt = Website::query()->count() / self::PAGINATION_CNT;
        $queued = 0;
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
                ++$queued;
            }
            ++$i;
        }
        $output->writeln("{$queued} websites queued to crawl");
    }
}
