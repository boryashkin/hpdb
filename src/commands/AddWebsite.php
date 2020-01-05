<?php
namespace app\commands;

use app\messageBus\messages\persistors\NewWebsiteToPersistMessage;
use app\modules\web\ProfileRepository;
use app\valueObjects\Url;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 *
 */
class AddWebsite extends Command
{
    /** @var \Jenssegers\Mongodb\Connection */
    private $mongo;
    /** @var MessageBusInterface */
    private $persistorsBus;

    public function setMongo(\Jenssegers\Mongodb\Connection $mongo)
    {
        $this->mongo = $mongo;
    }

    public function setPersistorsBus(MessageBusInterface $persistorsBus)
    {
        $this->persistorsBus = $persistorsBus;
    }

    /** @inheritDoc */
    protected function configure()
    {
        $this
            ->setName('service:add-website')
            ->setDescription('Add, reindex, extract data from the website. Use --url to provide an address')
            ->addOption('url', null, InputOption::VALUE_OPTIONAL, 'Url of website')
        ;
    }

    /** @inheritDoc */
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
            $output->writeln('Website already exists: ' . $website->_id);
            return 1;
        }
        $message = new NewWebsiteToPersistMessage($parsedUrl, 'cli', new \DateTime());
        $this->persistorsBus->dispatch($message);
        $output->writeln('Website is queued to be added');
    }

}
