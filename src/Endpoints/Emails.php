<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Helpers\Builder\EmailsParams;
use MailerSend\Helpers\GeneralHelpers;

class Emails extends AbstractEndpoint
{
    protected string $endpoint = 'emails';

    /**
     * Get a list of emails sent from a domain.
     *
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function getAll(EmailsParams $emailsParams): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($emailsParams->getDomainId(), 1, 'Domain id is required.')
        );

        GeneralHelpers::assert(
            fn () => Assertion::notEmpty($emailsParams->getDateFrom(), 'Date from is required.')
        );

        GeneralHelpers::assert(
            fn () => Assertion::notEmpty($emailsParams->getDateTo(), 'Date to is required.')
        );

        if (is_numeric($emailsParams->getDateFrom()) && is_numeric($emailsParams->getDateTo())) {
            GeneralHelpers::assert(
                fn () => Assertion::greaterThan(
                    $emailsParams->getDateTo(),
                    $emailsParams->getDateFrom(),
                    'Date to must be greater than date from.'
                )
            );
        }

        if ($emailsParams->getLimit()) {
            GeneralHelpers::assert(
                fn () => Assertion::range(
                    $emailsParams->getLimit(),
                    Constants::MIN_LIMIT,
                    Constants::MAX_LIMIT,
                    'Limit is supposed to be between ' . Constants::MIN_LIMIT . ' and ' . Constants::MAX_LIMIT . '.'
                )
            );
        }

        if (!empty($emailsParams->getStatus())) {
            $diff = array_diff($emailsParams->getStatus(), Constants::POSSIBLE_EMAIL_STATUSES);
            GeneralHelpers::assert(
                fn () => Assertion::count($diff, 0, 'The following statuses are invalid: ' . implode(', ', $diff))
            );
        }

        if (!empty($emailsParams->getInteraction())) {
            $diff = array_diff($emailsParams->getInteraction(), Constants::POSSIBLE_EMAIL_INTERACTIONS);
            GeneralHelpers::assert(
                fn () => Assertion::count($diff, 0, 'The following interactions are invalid: ' . implode(', ', $diff))
            );
        }

        if ($emailsParams->getRecipientEmail() !== null) {
            GeneralHelpers::assert(
                fn () => Assertion::email($emailsParams->getRecipientEmail(), 'Recipient email must be a valid email address.')
            );
        }

        if ($emailsParams->getSubject() !== null) {
            GeneralHelpers::assert(
                fn () => Assertion::minLength(
                    $emailsParams->getSubject(),
                    Constants::MIN_EMAIL_SUBJECT_FILTER_LENGTH,
                    'Subject must be at least ' . Constants::MIN_EMAIL_SUBJECT_FILTER_LENGTH . ' characters long.'
                )
            );
        }

        return $this->httpLayer->get($this->url($this->endpoint, $emailsParams->toArray()));
    }

    /**
     * Get a single email together with its activity events.
     *
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function find(string $emailId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($emailId, 1, 'Email id is required.')
        );

        return $this->httpLayer->get(
            $this->buildUri("email/$emailId")
        );
    }
}
