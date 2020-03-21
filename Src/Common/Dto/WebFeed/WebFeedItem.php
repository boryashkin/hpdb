<?php

declare(strict_types=1);

namespace App\Common\Dto\WebFeed;

use App\Common\Exceptions\InvalidUrlException;
use App\Common\ValueObjects\Url;
use Illuminate\Contracts\Support\Arrayable;
use MongoDB\BSON\ObjectId;
use MongoDB\Exception\InvalidArgumentException;

class WebFeedItem implements Arrayable
{
    private const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    public $title;
    public $description;
    public $date;
    public $website_id;
    public $link;
    public $language;

    public function __construct(array $data)
    {
        $this->title = $data['title'] ?? '';
        $this->description = $data['description'] ?? '';
        if (isset($data['date']) && $data['date']) {
            $this->date = $data['date'] instanceof \DateTimeInterface ? $data['date'] : new \DateTime($data['date']);
        }
        if (isset($data['website_id']) && $data['website_id']) {
            try {
                $this->website_id = $data['website_id'] instanceof ObjectId
                    ? $data['website_id']
                    : new ObjectId($data['website_id']);
            } catch (InvalidArgumentException $e) {
                unset($e);
            }
        }
        if (isset($data['link']) && $data['link']) {
            try {
                $this->link = $data['link'] instanceof Url ? $data['link'] : new Url($data['link']);
            } catch (InvalidUrlException $e) {
                unset($e);
            }
        }
        $this->language = (string)$data['language'];
    }

    public function toArray()
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'date' => $this->date ? $this->date->format(self::DATE_TIME_FORMAT) : null,
            'website_id' => $this->website_id ? (string)$this->website_id : null,
            'link' => $this->link ? (string)$this->link : null,
            'language' => $this->language,
        ];
    }
}
