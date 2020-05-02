<?php

declare(strict_types=1);

namespace App\Cli\Commands;

use App\Common\Models\Website;
use App\Common\Repositories\ProfileRepository;
use App\Common\Repositories\ReactionRepository;
use App\Common\Services\Website\WebsiteService;
use MongoDB\BSON\ObjectId;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class RecalculateWebsiteReactions extends Command
{
    /** @var \Jenssegers\Mongodb\Connection */
    private $mongo;
    /** @var ReactionRepository */
    private $reactionRepository;
    /** @var WebsiteService */
    private $websiteService;

    public function setMongo(\Jenssegers\Mongodb\Connection $mongo)
    {
        $this->mongo = $mongo;
    }

    public function recalculateReactions(Website $website): ?Website
    {
        $reactions = $this->getReactionRepository()->calculateRawReactionsByWebsiteId(new ObjectId($website->_id));
        $this->getWebsiteService()->assignReactions($website, $reactions);

        return $website;
    }

    /** {@inheritdoc} */
    protected function configure()
    {
        $this
            ->setName('service:recalculate-website-reactions')
            ->setDescription('Go through all of the HPs and assign reaction counters');
    }

    /** {@inheritdoc} */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start ' . date('H:i:s'));

        $i = 0;
        foreach ($this->getWebsiteService()->getAllCursor() as $website) {
            $website = $this->recalculateReactions($website);
            $reactions = $website->reactions;

            if (!($i++ % 100)) {
                $output->writeln($i . '] ' . implode(',', array_flip($reactions)));
            }
        }

        $output->writeln('Done ' . date('H:i:s'));
    }

    private function getReactionRepository(): ReactionRepository
    {
        if (!$this->reactionRepository) {
            $this->reactionRepository = new ReactionRepository($this->mongo);
        }

        return $this->reactionRepository;
    }

    private function getWebsiteService(): WebsiteService
    {
        if (!$this->websiteService) {
            $this->websiteService = new WebsiteService(new ProfileRepository($this->mongo));
        }

        return $this->websiteService;
    }
}
