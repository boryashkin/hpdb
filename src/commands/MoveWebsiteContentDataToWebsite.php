<?php

namespace app\commands;

use app\models\WebsiteContent;
use app\modules\web\ProfileRepository;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */
class MoveWebsiteContentDataToWebsite extends Command
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
            ->setName('service:move-website-content-to-website')
            ->setDescription('One time job: get rid of WebsiteContent');
    }

    /** @inheritDoc */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = new ProfileRepository($this->mongo);
        $q = WebsiteContent::query()->get();
        $i = 0;
        /** @var WebsiteContent $row */
        foreach ($q as $row) {
            $website = $repo->getOneById($row->website_id);
            if (!$website) {
                $output->writeln("Website $row[website_id] not found");
                continue;
            }
            $obj = (object)$row->toArray();
            $obj->updated_at = new UTCDateTime((new \DateTime($obj->updated_at))->getTimestamp() * 1000);
            $obj->created_at = new UTCDateTime((new \DateTime($obj->created_at))->getTimestamp() * 1000);
            $obj->from_website_index_history_id = new ObjectId($obj->from_website_index_history_id);
            unset($obj->_id);
            unset($obj->website_id);
            $website->content = $obj;
            $website->save();
            if ($i % 100) {
                $output->writeln($i++);
            }
        }

        $output->writeln('Done');
    }

}
