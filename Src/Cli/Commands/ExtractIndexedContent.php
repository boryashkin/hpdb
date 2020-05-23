<?php

namespace App\Cli\Commands;

use App\Common\Exceptions\InvalidUrlException;
use App\Common\MessageBus\Messages\Processors\WebsiteHistoryMessage;
use App\Common\Models\WebsiteIndexHistory;
use App\Common\Repositories\ProfileRepository;
use App\Common\Repositories\WebsiteIndexHistoryRepository;
use App\Common\Repositories\WebsiteRepository;
use App\Common\ValueObjects\Url;
use MongoDB\BSON\ObjectId;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Messenger\MessageBusInterface;

class ExtractIndexedContent extends Command
{
    /** @var \Jenssegers\Mongodb\Connection */
    private $mongo;
    /** @var MessageBusInterface */
    private $processorBus;

    public function setMongo(\Jenssegers\Mongodb\Connection $mongo): void
    {
        $this->mongo = $mongo;
    }

    public function setProcessorBus(MessageBusInterface $bus): void
    {
        $this->processorBus = $bus;
    }

    public function extractAndSave(WebsiteIndexHistory $websiteHistory): bool
    {
        if (!$websiteHistory->content) {
            return false;
        }
        $crawler = new Crawler($websiteHistory->content);
        $repo = new ProfileRepository($this->mongo);
        $website = $repo->getOneById($websiteHistory->website_id);
        $content = new \stdClass();
        $content->title = null;
        $content->description = null;
        $title = $crawler->filterXPath('//title');
        if ($title->count()) {
            $content->title = $title->text();
        }
        foreach ($crawler->filterXPath("//meta[@name='description']/@content") as $t) {
            $content->description = $t->textContent;
        }
        $website->content = $content;

        return $website->save();
    }

    /** {@inheritdoc} */
    protected function configure()
    {
        $this
            ->setName('service:extract-indexed-content')
            ->setDescription('Extracting valuable info from the indexed html list.');
    }

    /** {@inheritdoc} */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = new WebsiteIndexHistoryRepository($this->mongo);
        $websiteRepo = new WebsiteRepository($this->mongo);

        $lastId = null;
        do {
            $limit = 1000;
            $i = 0;
            foreach ($repo->getAllCursor($lastId, SORT_DESC, $limit) as $hist) {
                $i++;
                $website = $websiteRepo->getOne(new ObjectId($hist->website_id));
                if (!$website || !$website->homepage) {
                    continue;
                }
                try {
                    $message = new WebsiteHistoryMessage(new ObjectId($hist->website_id), new ObjectId($hist->_id), new Url($website->homepage), $hist->content, $hist->initial_encoding);
                } catch (InvalidUrlException $e) {
                    $output->writeln('InvalidUrl: ' . $website->homepage);
                    continue;
                }
                $this->processorBus->dispatch($message);
                $lastId = new ObjectId($hist->_id);
                $output->writeln($hist->_id);
            }
            $proceed = $limit !== $i;
        } while ($proceed);

        $output->writeln('end');
    }
}
