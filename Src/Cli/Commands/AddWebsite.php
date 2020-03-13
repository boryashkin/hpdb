<?php

namespace App\Cli\Commands;

use App\Common\MessageBus\Messages\Crawlers\NewWebsiteToCrawlMessage;
use App\Common\MessageBus\Messages\Persistors\NewWebsiteToPersistMessage;
use App\Common\Models\WebsiteIndexHistory;
use App\Common\Repositories\ProfileRepository;
use App\Common\ValueObjects\Url;
use MongoDB\BSON\ObjectId;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AddWebsite extends Command
{
    /** @var \Jenssegers\Mongodb\Connection */
    private $mongo;
    /** @var MessageBusInterface */
    private $persistorsBus;
    /** @var MessageBusInterface */
    private $crawlersBus;

    public function setMongo(\Jenssegers\Mongodb\Connection $mongo)
    {
        $this->mongo = $mongo;
    }

    public function setPersistorsBus(MessageBusInterface $persistorsBus)
    {
        $this->persistorsBus = $persistorsBus;
    }

    public function setCrawlersBus(MessageBusInterface $crawlersBus)
    {
        $this->crawlersBus = $crawlersBus;
    }

    /** {@inheritdoc} */
    protected function configure()
    {
        $this
            ->setName('service:add-website')
            ->setDescription('Add, reindex, extract data from the website. Use --url to provide an address')
            ->addOption('url', null, InputOption::VALUE_OPTIONAL, 'Url of website');
    }

    /** {@inheritdoc} */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('url')) {
            $websiteUrl = (string)$input->getOption('url');
            $parsedUrl = new Url($websiteUrl);
        } else {
            $output->writeln('No url provided');

            return 1;
        }

        $repo = new ProfileRepository($this->mongo);
        $website = $repo->getFirstOneByUrl($parsedUrl);
        if ($website) {
            $hist = WebsiteIndexHistory::query()->where('website_id', $website->_id)->first();
            if ($hist) {
                $output->writeln('Website already exists: ' . $website->_id);

                return 1;
            }
            $message = new NewWebsiteToCrawlMessage(new ObjectId($website->_id), $parsedUrl);
            $this->crawlersBus->dispatch($message);

            return 1;
        }
        $message = new NewWebsiteToPersistMessage($parsedUrl, 'cli', new \DateTime());
        $this->persistorsBus->dispatch($message);
        $output->writeln('Website is queued to be added');
    }
}
