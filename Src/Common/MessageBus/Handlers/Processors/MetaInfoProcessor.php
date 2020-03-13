<?php

namespace App\Common\MessageBus\Handlers\Processors;

use App\Common\MessageBus\Messages\Persistors\WebsiteMetaInfoMessage;
use App\Common\MessageBus\Messages\Processors\WebsiteHistoryMessage;
use MongoDB\BSON\ObjectId;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Extracting title, description and og fields.
 */
class MetaInfoProcessor implements ProcessorInterface
{
    /** @var MessageBusInterface */
    private $persistorsBus;
    private $name;

    public function __construct(string $name, MessageBusInterface $persistorsBus)
    {
        $this->name = $name;
        $this->persistorsBus = $persistorsBus;
    }

    public function __invoke(WebsiteHistoryMessage $historyMessage)
    {
        $message = $this->createMetaInfoMessage(
            new ObjectId($historyMessage->getWebsiteId()),
            new ObjectId($historyMessage->getIndexHistoryId()),
            $historyMessage->getInitialEncoding(),
            $historyMessage->getContent()
        );
        $this->persistorsBus->dispatch($message);
    }

    private function createMetaInfoMessage(
        ObjectId $websiteId,
        ObjectId $historyIndexId,
        ?string $initialEncoding,
        string $content
    ): WebsiteMetaInfoMessage
    {
        if ($initialEncoding) {
            $content = \iconv('UTF-8', $initialEncoding, $content);
            //awful hack to handle the Crawler encoding conversions based on an absence of meta-charset
            if (!preg_match('/\<meta[^\>]+charset *= *["\']?([a-zA-Z\-0-9_:.]+)/i', $content, $matches)) {
                $pos = stripos($content, '<head>');
                if ($pos !== false) {
                    $endOfHeadTag = $pos + 6;
                    $content = substr($content, 0, $endOfHeadTag)
                        . "<meta charset=\"{$initialEncoding}\">"
                        . substr($content, $endOfHeadTag);
                }
            }
        }
        $crawler = new Crawler($content);
        $titlePath = $crawler->filterXPath('//title');
        $title = null;
        $description = null;
        if ($titlePath->count()) {
            $title = $titlePath->text();
        }
        foreach ($crawler->filterXPath("//meta[@name='description']/@content") as $t) {
            $description = $t->textContent;
        }

        return new WebsiteMetaInfoMessage($websiteId, $historyIndexId, $title, $description);
    }
}
