<?php

namespace Capco\AppBundle\Manager;

use Doctrine\ORM\EntityManager;
use Gedmo\Loggable\Entity\LogEntry;
use Sonata\UserBundle\Entity\UserManager;
use Symfony\Component\Translation\TranslatorInterface;
use Capco\AppBundle\Entity\Synthesis\SynthesisDivision;
use Capco\AppBundle\Entity\Synthesis\SynthesisElement;

class LogManager
{
    protected $translator;
    protected $userManager;
    protected $em;

    public function __construct(TranslatorInterface $translator, UserManager $userManager, EntityManager $em)
    {
        $this->translator = $translator;
        $this->userManager = $userManager;
        $this->em = $em;
    }

    public function getSentencesForLog(LogEntry $log)
    {
        $sentences = array();
        $username = $this->userManager->findOneBy(array('slug' => $log->getUsername()));
        $elementName = $log->getObjectId();

        // Update actions
        if ($log->getAction() === 'update') {
            if (array_key_exists('parent', $log->getData())) {
                $sentences[] = $this->makeSentence('move', $username, $elementName);
            }
            if (array_key_exists('published', $log->getData())) {
                if ($log->getData()['published'] === true) {
                    $sentences[] = $this->makeSentence('publish', $username, $elementName);
                } else {
                    $sentences[] = $this->makeSentence('unpublish', $username, $elementName);
                }
            }
            if (array_key_exists('archived', $log->getData()) && $log->getData()['archived'] === true) {
                $sentences[] = $this->makeSentence('archive', $username, $elementName);
            }
            if (array_key_exists('notation', $log->getData())) {
                $sentences[] = $this->makeSentence('note', $username, $elementName);
            }
            if (array_key_exists('comment', $log->getData())) {
                $sentences[] = $this->makeSentence('comment', $username, $elementName);
            }
            if (array_key_exists('title', $log->getData()) || array_key_exists('body', $log->getData())) {
                $sentences[] = $this->makeSentence('update', $username, $elementName);
            }
            if (array_key_exists('division', $log->getData())) {
                $sentences[] = $this->makeSentence('divide', $username, $elementName);
            }

            return $sentences;
        }

        // Delete or create actions
        $sentences[] = $this->makeSentence($log->getAction(), $username, $elementName);

        return $sentences;
    }

    public function getLogEntries($entity)
    {
        return $this->em->getRepository('GedmoLoggable:LogEntry')->getLogEntries($entity);
    }

    public function makeSentence($action, $username, $elementName)
    {
        $transBase = 'synthesis.logs.sentence.';

        return $this->translator->trans($transBase.$action, array(
            '%author%' => $username,
            '%element%' => $elementName,
        ), 'CapcoAppBundleSynthesis');
    }
}
