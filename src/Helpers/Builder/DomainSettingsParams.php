<?php

namespace MailerSend\Helpers\Builder;

class DomainSettingsParams
{
    protected ?bool $send_paused = null;
    protected ?bool $track_clicks = null;
    protected ?bool $track_opens = null;
    protected ?bool $track_unsubscribe = null;
    protected ?bool $track_content = null;
    protected ?string $track_unsubscribe_html = null;
    protected ?string $track_unsubscribe_plain = null;
    protected ?bool $custom_tracking_enabled = null;
    protected ?string $custom_tracking_subdomain = null;
    protected ?bool $precedence_bulk = null;
    protected ?bool $ignore_duplicated_recipients = null;

    public function getSendPaused(): ?bool
    {
        return $this->send_paused;
    }

    public function setSendPaused(?bool $send_paused): DomainSettingsParams
    {
        $this->send_paused = $send_paused;
        return $this;
    }

    public function getTrackClicks(): ?bool
    {
        return $this->track_clicks;
    }

    public function setTrackClicks(?bool $track_clicks): DomainSettingsParams
    {
        $this->track_clicks = $track_clicks;
        return $this;
    }

    public function getTrackOpens(): ?bool
    {
        return $this->track_opens;
    }

    public function setTrackOpens(?bool $track_opens): DomainSettingsParams
    {
        $this->track_opens = $track_opens;
        return $this;
    }

    public function getTrackUnsubscribe(): ?bool
    {
        return $this->track_unsubscribe;
    }

    public function setTrackUnsubscribe(?bool $track_unsubscribe): DomainSettingsParams
    {
        $this->track_unsubscribe = $track_unsubscribe;
        return $this;
    }

    public function getTrackContent(): ?bool
    {
        return $this->track_content;
    }

    public function setTrackContent(?bool $track_content): DomainSettingsParams
    {
        $this->track_content = $track_content;
        return $this;
    }

    public function getTrackUnsubscribeHtml(): ?string
    {
        return $this->track_unsubscribe_html;
    }

    public function setTrackUnsubscribeHtml(?string $track_unsubscribe_html): DomainSettingsParams
    {
        $this->track_unsubscribe_html = $track_unsubscribe_html;
        return $this;
    }

    public function getTrackUnsubscribePlain(): ?string
    {
        return $this->track_unsubscribe_plain;
    }

    public function setTrackUnsubscribePlain(?string $track_unsubscribe_plain): DomainSettingsParams
    {
        $this->track_unsubscribe_plain = $track_unsubscribe_plain;
        return $this;
    }

    public function getCustomTrackingEnabled(): ?bool
    {
        return $this->custom_tracking_enabled;
    }

    public function setCustomTrackingEnabled(?bool $custom_tracking_enabled): DomainSettingsParams
    {
        $this->custom_tracking_enabled = $custom_tracking_enabled;
        return $this;
    }

    public function getCustomTrackingSubdomain(): ?string
    {
        return $this->custom_tracking_subdomain;
    }

    public function setCustomTrackingSubdomain(?string $custom_tracking_subdomain): DomainSettingsParams
    {
        $this->custom_tracking_subdomain = $custom_tracking_subdomain;
        return $this;
    }

    public function getPrecedenceBulk(): ?bool
    {
        return $this->precedence_bulk;
    }

    public function setPrecedenceBulk(?bool $precedence_bulk): DomainSettingsParams
    {
        $this->precedence_bulk = $precedence_bulk;
        return $this;
    }

    public function getIgnoreDuplicatedRecipients(): ?bool
    {
        return $this->ignore_duplicated_recipients;
    }

    public function setIgnoreDuplicatedRecipients(?bool $ignore_duplicated_recipients): DomainSettingsParams
    {
        $this->ignore_duplicated_recipients = $ignore_duplicated_recipients;
        return $this;
    }

    public function toArray(): array
    {
        return [
             'send_paused' => $this->getSendPaused(),
             'track_clicks' => $this->getTrackClicks(),
             'track_opens' => $this->getTrackOpens(),
             'track_unsubscribe' => $this->getTrackUnsubscribe(),
             'track_content' => $this->getTrackContent(),
             'track_unsubscribe_html' => $this->getTrackUnsubscribeHtml(),
             'track_unsubscribe_plain' => $this->getTrackUnsubscribePlain(),
             'custom_tracking_enabled' => $this->getCustomTrackingEnabled(),
             'custom_tracking_subdomain' => $this->getCustomTrackingSubdomain(),
             'precedence_bulk' => $this->getPrecedenceBulk(),
             'ignore_duplicated_recipients' => $this->getIgnoreDuplicatedRecipients(),
        ];
    }
}
