<?php namespace Hampel\Symfony\Mailer\SparkPost\Mime;

use Symfony\Component\Mime\Email;

class SparkPostEmail extends Email
{
    use HasMetadataTrait;
    use HasSubstitutionDataTrait;

    /**
     * @var string|null
     */
    private $campaignId;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var array|null [from, subject, text, html, amp_html, reply_to, headers, attachments, inline_images]
     */
    private $content;

//    public function ensureValidity()
//    {
//    }

    public function getCampaignId(): ?string
    {
        return $this->campaignId;
    }

    public function setCampaignId(?string $campaignId): SparkPostEmail
    {
        $this->campaignId = $campaignId;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): SparkPostEmail
    {
        $this->description = $description;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): SparkPostEmail
    {
        $this->options = $options;

        return $this;
    }

    public function setTransactional(bool $transactional = true): SparkPostEmail
    {
        $this->options['transactional'] = $transactional;

        return $this;
    }

    public function setClickTracking(bool $clickTracking = true): SparkPostEmail
    {
        $this->options['click_tracking'] = $clickTracking;

        return $this;
    }

    public function setOpenTracking(bool $openTracking = true): SparkPostEmail
    {
        $this->options['open_tracking'] = $openTracking;

        return $this;
    }

    public function getContent(): ?array
    {
        return $this->content;
    }

    public function setContent(?array $content): SparkPostEmail
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @internal
     */
    public function __serialize(): array
    {
        return [
            $this->campaignId,
            $this->description,
            $this->options,
            $this->content,
            $this->substitutionData,
            $this->metadata,
            parent::__serialize(),
        ];
    }

    /**
     * @internal
     */
    public function __unserialize(array $data): void
    {
        [
            $this->campaignId,
            $this->description,
            $this->options,
            $this->content,
            $this->substitutionData,
            $this->metadata,
            $parentData,
        ] = $data;

        parent::__unserialize($parentData);
    }
}
