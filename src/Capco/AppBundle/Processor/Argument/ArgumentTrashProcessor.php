<?php

namespace Capco\AppBundle\Processor\Argument;

use Capco\AppBundle\Notifier\ArgumentNotifier;
use Capco\AppBundle\Repository\ArgumentRepository;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;

class ArgumentTrashProcessor implements ProcessorInterface
{
    private $argumentRepository;
    private $argumentNotifier;

    public function __construct(ArgumentRepository $argumentRepository, ArgumentNotifier $argumentNotifier)
    {
        $this->argumentRepository = $argumentRepository;
        $this->argumentNotifier = $argumentNotifier;
    }

    public function process(Message $message, array $options)
    {
        $json = json_decode($message->getBody(), true);
        $argument = $this->argumentRepository->find($json['argumentId']);
        if (!$argument) {
            throw new \RuntimeException('Unable to find argument with id : ' . $id);
        }
        $this->argumentNotifier->onTrash($argument);

        return true;
    }
}
