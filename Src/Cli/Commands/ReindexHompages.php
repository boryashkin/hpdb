<?php

namespace App\Cli\Commands;

use App\Common\Exceptions\InvalidUrlException;
use App\Common\MessageBus\Messages\Crawlers\NewWebsiteToCrawlMessage;
use App\Common\Models\Website;
use App\Common\Repositories\ProfileRepository;
use App\Common\Services\Website\WebsiteService;
use App\Common\ValueObjects\Url;
use Jenssegers\Mongodb\Connection;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Exception\InvalidArgumentException;
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

    /** @var Connection */
    private $mongo;
    /** @var MessageBusInterface */
    private $crawlersBus;

    public function setMongo(Connection $mongo)
    {
        $this->mongo = $mongo;
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
            $url = Url::createFromNormalized($website->scheme, $website->homepage);
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
            ->addOption('skip', null, InputOption::VALUE_OPTIONAL, 'skip first qty of websites')
            ->addOption('website-id', null, InputOption::VALUE_OPTIONAL, 'specific id to reindex');
    }

    /** {@inheritdoc} */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(date('H:i:s') . ' [started]');

        if ($input->getOption('website-id')) {
            $this->handleOne($input, $output);
        } else {
            $this->handleMultiple($input, $output);
        }

        $output->writeln(date('H:i:s') . ' [done]');

        return 0;
    }

    private function handleOne(InputInterface $input, OutputInterface $output): void
    {
        try {
            $websiteId = new ObjectId($input->getOption('website-id'));
        } catch (InvalidArgumentException $exception) {
            $output->writeln('Invalid id');
            return;
        }

        $repo = new ProfileRepository($this->mongo);
        $service = new WebsiteService($repo);
        $website = $service->getOneById($websiteId);

        if (!$website) {
            $this->reindex($website);
        }
    }

    private function handleMultiple(InputInterface $input, OutputInterface $output): void
    {
        if ($input->getOption('skip')) {
            $i = (int)$input->getOption('skip');
        } else {
            $i = 0;
        }
        $websites = true;
        $cnt = Website::query()->count() / self::PAGINATION_CNT;
        $queued = 0;
        $yesterday = new \DateTime('yesterday');
        while ($websites && $i <= $cnt) {
            $websites = Website::query()
                ->offset($i * self::PAGINATION_CNT)
                ->take(self::PAGINATION_CNT)
                ->get()
                ->all();
            /** @var Website $website */
            foreach ($websites as $website) {
                if ($website->updated_at->toDateTime() > $yesterday) {
                    continue;
                }
                $this->reindex($website);
                ++$queued;
            }
            ++$i;
        }
        $output->writeln("{$queued} websites queued to crawl");
    }
}
