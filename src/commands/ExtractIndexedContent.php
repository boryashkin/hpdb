<?php

namespace app\commands;

use app\messageBus\messages\processors\WebsiteHistoryMessage;
use app\models\WebsiteIndexHistory;
use app\modules\web\ProfileRepository;
use app\valueObjects\Url;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection as MongoCollection;
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
        /**
         * Not WebsiteIndexHistory::query() because of memory leaks.
         *
         * @var MongoCollection $c
         */
        $c = $this->mongo->getCollection('websiteIndexHistory');
        $skip = 0;
        $limit = 50;
        $pipeline = [
            0 => [
                '$project' => [
                    'website_id' => 1,
                    'created_at' => 1,
                    'available' => 1,
                ],
            ],
            1 => [
                '$match' => [
                    'available' => ['$eq' => true],
                ],
            ],
            2 => [
                '$group' => [
                    '_id' => ['website_id' => '$website_id'],
                    'created_at' => ['$max' => '$created_at'],
                ],
            ],
            3 => [
                '$skip' => $skip,
            ],
            4 => [
                '$limit' => $limit,
            ],
        ];
        do {
            $pipeline[3]['$skip'] = $skip;
            $lastWebsiteData = [];
            foreach ($c->aggregate($pipeline) as $website) {
                $lastWebsiteData[] = ['$and' => [['website_id' => $website->_id->website_id], ['created_at' => $website->created_at]]];
            }
            if (!$lastWebsiteData) {
                break;
            }
            foreach ($c->find(['$or' => $lastWebsiteData]) as $website) {
                $hist = new WebsiteIndexHistory();
                $hist->forceFill((array)$website);
                $message = new WebsiteHistoryMessage(new ObjectId($hist->website_id), new ObjectId($hist->_id), new Url($website->homepage), $hist->content, $hist->initial_encoding);
                $this->processorBus->dispatch($message);
            }
            $skip = $skip + $limit;
        } while ($lastWebsiteData);
    }
}
