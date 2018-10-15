<?php

namespace Capco\AppBundle\Entity\Questions;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\SimpleQuestionRepository")
 */
class SimpleQuestion extends AbstractQuestion
{
    public static $questionTypesLabels = [
        'question.types.text' => self::QUESTION_TYPE_SIMPLE_TEXT,
        'question.types.textarea' => self::QUESTION_TYPE_MULTILINE_TEXT,
        'question.types.editor' => self::QUESTION_TYPE_EDITOR,
    ];

    public function __construct()
    {
        parent::__construct();
        // Simple questions can't be of those types, we unset them
        unset(
            self::$questionTypesInputs[self::QUESTION_TYPE_RADIO],
            self::$questionTypesInputs[self::QUESTION_TYPE_SELECT],
            self::$questionTypesInputs[self::QUESTION_TYPE_CHECKBOX],
            self::$questionTypesInputs[self::QUESTION_TYPE_MEDIAS]
        );
    }

    public function isSimpleQuestion(): bool
    {
        return true;
    }
}
