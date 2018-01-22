<?php

namespace Capco\AppBundle\Mailer\Message;

class ExternalMessage extends Message
{
    protected $sitename;

    public function getFooterTemplate(): string
    {
        return 'notification.email.external_footer';
    }

    public function getFooterVars(): array
    {
        return [
            '%to%' => self::escape($this->getRecipient(0)->getEmailAddress()),
            '%sitename%' => $this->getSitename() ? self::escape($this->getSitename()) : 'Cap Collectif',
        ];
    }

    public function setSitename(string $value)
    {
        $this->sitename = $value;
    }

    public function getSitename()//:?string
    {
        return $this->sitename;
    }
}
