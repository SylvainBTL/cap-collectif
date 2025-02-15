<?php

namespace Capco\AppBundle\Processor\Sms;

use Capco\AppBundle\Notifier\SmsNotifier ;
use Capco\AppBundle\Repository\SmsOrderRepository ;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;

class SmsCreditRefillOrderProcessor implements ProcessorInterface
{
    private SmsOrderRepository $smsOrderRepository;
    private SmsNotifier $notifier;

    public function __construct(SmsOrderRepository $smsOrderRepository, SmsNotifier $notifier)
    {
        $this->smsOrderRepository = $smsOrderRepository;
        $this->notifier = $notifier;
    }

    public function process(Message $message, array $options): bool
    {
        $json = json_decode($message->getBody(), true);
        $id = $json['smsOrderId'];
        $smsOrder = $this->smsOrderRepository->find($id);
        if (!$smsOrder) {
            throw new \RuntimeException('Unable to find sms_order with id : ' . $id);
        }

        $this->notifier->onRefillSmsOrder($smsOrder);

        return true;
    }
}
