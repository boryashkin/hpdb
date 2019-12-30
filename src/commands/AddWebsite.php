<?php
namespace app\commands;

use app\models\Website;
use app\models\WebsiteIndexHistory;
use app\modules\web\ProfileRepository;
use Guzzle\Http\Url;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */
class AddWebsite extends Command
{
    /** @var \Jenssegers\Mongodb\Connection */
    private $mongo;
    /**
     * @deprecated
     * @var ReindexHompages
     */
    private $reindexer;

    public function setMongo(\Jenssegers\Mongodb\Connection $mongo)
    {
        $this->mongo = $mongo;
    }

    /** @deprecated Добавлена как один из шагов рефакторинга */
    public function setReindexer(ReindexHompages $reindexHompages)
    {
        $this->reindexer = $reindexHompages;
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
            $parsedUrl = Url::factory($websiteUrl);
        } else {
            $output->writeln('No url provided');
            return;
        }

        $repo = new ProfileRepository($this->mongo);
        $website = $repo->getFirstOneByUrl($parsedUrl);
        $maxWebsite = Website::query()->orderBy('profile_id', 'desc')->limit(1)->first();
        if (!$website) {
            $website = new Website();
            $website->homepage = $websiteUrl;
            $website->profile_id = $maxWebsite->profile_id + 1;
            $website->save();
            $output->writeln('Website saved: profile_id = ' . $website->profile_id);
        } else {
            $output->writeln('Website found: profile_id = ' . $website->profile_id);
        }
        $indexer = $this->reindexer;
        $indexer->reindex($website);
        $output->writeln('Attemted to index the website');

        /** @var WebsiteIndexHistory $hist */
        $hist = WebsiteIndexHistory::query()
            ->where('website_id', '=', $website->getAttributes()['_id'])
            ->orderBy('created_at', 'desc')
            ->limit(1)->first();
        if ($hist) {
            $extractor = new ExtractIndexedContent();
            $extractor->setMongo($this->mongo);
            $extractor->extractAndSave($hist);
            $output->writeln('Extracted and saved info about the website');
        } else {
            $output->writeln('Index record is not found');
        }
    }

}
