<?php

declare(strict_types=1);
namespace In2code\Powermail\Events;

use In2code\Powermail\Domain\Service\Mail\SendMailService;
use TYPO3\CMS\Core\View\ViewInterface;

final class SendMailServiceCreateEmailBodyEvent
{
    public function __construct(protected ViewInterface $standaloneView, protected array $email, protected SendMailService $sendMailService)
    {
    }

    public function getStandaloneView(): ViewInterface
    {
        return $this->standaloneView;
    }

    public function setStandaloneView(ViewInterface $standaloneView): SendMailServiceCreateEmailBodyEvent
    {
        $this->standaloneView = $standaloneView;
        return $this;
    }

    public function getEmail(): array
    {
        return $this->email;
    }

    public function setEmail(array $email): SendMailServiceCreateEmailBodyEvent
    {
        $this->email = $email;
        return $this;
    }

    public function getSendMailService(): SendMailService
    {
        return $this->sendMailService;
    }
}
