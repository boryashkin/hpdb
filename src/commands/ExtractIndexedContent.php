<?php
namespace app\commands;

use app\models\WebsiteContent;
use MongoDB\Collection as MongoCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 *
 */
class ExtractIndexedContent extends Command
{
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
            ->setName('service:extract-indexed-content')
            ->setDescription('Extracting valuable info from the indexed html list.')
        ;
    }

    /** @inheritDoc */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * Not WebsiteIndexHistory::query() because of memory leaks
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
                ]
            ],
            1 => [
                '$match' => [
                    'available' => ['$eq' => true],
                ]
            ],
            2 => [
                '$group' => [
                    '_id' => ['website_id' => '$website_id'],
                    'created_at' => ['$max' => '$created_at'],
                ]
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
                if (!$website->content) {
                    continue;
                }
                $crawler = new Crawler($website->content);
                $content = new WebsiteContent();
                $content->website_id = $website->website_id;
                $content->title = null;
                $content->description = null;
                $title = $crawler->filterXPath('//title');
                if ($title->count()) {
                    $content->title = $title->text();
                }
                foreach ($crawler->filterXPath("//meta[@name='description']/@content") as $t) {
                    $content->description = $t->textContent;
                }
                $content->save();
            }
            $skip = $skip + $limit;
        } while ($lastWebsiteData);
    }

}
