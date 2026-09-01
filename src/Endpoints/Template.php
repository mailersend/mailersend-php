<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Helpers\Builder\TemplateParams;
use MailerSend\Helpers\GeneralHelpers;

class Template extends AbstractEndpoint
{
    protected string $endpoint = 'templates';

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \JsonException
     */
    public function getAll(?string $domainId = null, ?int $page = null, ?int $limit = Constants::DEFAULT_LIMIT): array
    {
        if ($limit) {
            GeneralHelpers::assert(
                fn () => Assertion::range(
                    $limit,
                    Constants::MIN_LIMIT,
                    Constants::MAX_LIMIT,
                    'Limit is supposed to be between ' . Constants::MIN_LIMIT . ' and ' . Constants::MAX_LIMIT .  '.'
                )
            );
        }

        return $this->httpLayer->get(
            $this->buildUri($this->endpoint, [
                'domain_id' => $domainId,
                'page' => $page,
                'limit' => $limit,
            ])
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function find(string $templateId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($templateId, 1, 'Template id is required.')
        );

        return $this->httpLayer->get(
            $this->buildUri("$this->endpoint/$templateId")
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function create(TemplateParams $params): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::notEmpty($params->getHtml(), 'HTML is required.')
        );

        if ($params->getName() !== null) {
            GeneralHelpers::assert(
                fn () => Assertion::maxLength($params->getName(), 50, 'Name must be 50 characters or fewer.')
            );
        }

        if ($params->getAutoGenerate() !== true) {
            GeneralHelpers::assert(
                fn () => Assertion::notEmpty($params->getText(), 'Text is required when auto_generate is not true.')
            );
        }

        if ($params->getTags() !== null && count($params->getTags()) > 0) {
            GeneralHelpers::assert(
                fn () => Assertion::maxCount($params->getTags(), 5, 'Tags list should not contain more than 5 items.')
            );
            foreach ($params->getTags() as $tag) {
                GeneralHelpers::assert(
                    fn () => Assertion::maxLength($tag, 191, 'Each tag may not be greater than 191 characters.')
                );
            }
        }

        return $this->httpLayer->post(
            $this->buildUri($this->endpoint),
            $params->toArray()
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function update(string $templateId, TemplateParams $params): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($templateId, 1, 'Template id is required.')
        );

        if ($params->getName() !== null) {
            GeneralHelpers::assert(
                fn () => Assertion::maxLength($params->getName(), 50, 'Name must be 50 characters or fewer.')
            );
        }

        if ($params->getTags() !== null && count($params->getTags()) > 0) {
            GeneralHelpers::assert(
                fn () => Assertion::maxCount($params->getTags(), 5, 'Tags list should not contain more than 5 items.')
            );
            foreach ($params->getTags() as $tag) {
                GeneralHelpers::assert(
                    fn () => Assertion::maxLength($tag, 191, 'Each tag may not be greater than 191 characters.')
                );
            }
        }

        return $this->httpLayer->put(
            $this->buildUri("$this->endpoint/$templateId"),
            $params->toArray()
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function delete(string $templateId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($templateId, 1, 'Template id is required.')
        );

        return $this->httpLayer->delete(
            $this->buildUri("$this->endpoint/$templateId")
        );
    }
}
