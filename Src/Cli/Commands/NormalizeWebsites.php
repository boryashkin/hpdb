<?php

namespace App\Cli\Commands;

use App\Common\Exceptions\InvalidUrlException;
use App\Common\Models\Website;
use App\Common\Repositories\WebsiteRepository;
use App\Common\ValueObjects\Url;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Make all websites to follow the strict rules
 */
class NormalizeWebsites extends Command
{
    /** @var \Jenssegers\Mongodb\Connection */
    private $mongo;

    public function setMongo(\Jenssegers\Mongodb\Connection $mongo)
    {
        $this->mongo = $mongo;
    }

    public function normalizeHomepage(Website $website): ?Website
    {
        try {
            $url = new Url($website->homepage);
            $website->homepage = $url->getNormalized();
            $website->scheme = $url->getScheme();
        } catch (InvalidUrlException | \TypeError $e) {

        }

        return $website;
    }

    /** {@inheritdoc} */
    protected function configure()
    {
        $this
            ->setName('service:normalize-websites')
            ->setDescription('Go through all of the HPs and fix all the fields');
    }

    /** {@inheritdoc} */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = new WebsiteRepository($this->mongo);

        $i = 0;
        $output->writeln('Start ' . date('H:i:s'));
        /** @var Website $website */
        foreach ($repo->getAllCursor() as $website) {
            if ($website->scheme) {
                continue;
            }
            $website = $this->normalizeHomepage($website);
            if (!$repo->save($website)) {
                $output->writeln("Unable to save {$website->_id}");
            }

            if (!($i++ % 1000)) {
                $output->writeln("Handled $i websites");
            }
        }
        $output->writeln("Handled $i websites");
        $output->writeln('Done ' . date('H:i:s'));
    }
}
