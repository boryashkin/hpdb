<?php

namespace App\Cli\Commands;

use App\Common\Models\Website;
use App\Common\Repositories\Filters\WebsiteFilter;
use App\Common\Repositories\WebsiteIndexHistoryRepository;
use App\Common\Repositories\WebsiteRepository;
use MongoDB\BSON\ObjectId;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MoveWebsiteIndexDataToWebsite extends Command
{
    /** @var \Jenssegers\Mongodb\Connection */
    private $mongo;

    public function setMongo(\Jenssegers\Mongodb\Connection $mongo)
    {
        $this->mongo = $mongo;
    }

    /** {@inheritdoc} */
    protected function configure()
    {
        $this
            ->setName('service:move-website-index-to-website')
            ->setDescription('One time job: assign website_index_history to website');
    }

    /** {@inheritdoc} */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $i = 0;
        $websiteRepository = new WebsiteRepository($this->mongo);
        $indexRepository = new WebsiteIndexHistoryRepository($this->mongo);
        $filter = new WebsiteFilter();
        $filter->contentFromWebsiteIndexHistoryIdExists = false;
        $indexCursor = $websiteRepository->getCursorByFilter($filter);

        $output->writeln('before');
        /** @var Website $website */
        foreach ($indexCursor as $website) {
            $output->writeln('iteration');
            $index = $indexRepository->getLastByWebsiteId(new ObjectId($website->_id));
            $content = $website->content;
            $content['from_website_index_history_id'] = new ObjectId($index->_id);
            $website->content = $content;
            $websiteRepository->save($website);
            if ($i % 100) {
                $output->writeln($i++);
            }
        }

        $output->writeln('Done');
    }
}
