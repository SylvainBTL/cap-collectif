<?php

namespace Capco\AppBundle\Entity;

use Capco\AppBundle\Traits\UuidTrait;
use Capco\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="user_notifications_configuration")
 * @ORM\Entity()
 */
class UserNotificationsConfiguration
{
    use UuidTrait;

    /**
     * @var User
     * @ORM\OneToOne(targetEntity="Capco\UserBundle\Entity\User", mappedBy="notificationsConfiguration")
     */
    private $user;

    /**
     * @var bool
     * @ORM\Column(name="on_proposal_comment_mail", type="boolean", options={"default": true})
     */
    private $onProposalCommentMail = true;

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return bool
     */
    public function isOnProposalCommentMail(): bool
    {
        return $this->onProposalCommentMail;
    }

    /**
     * @param bool $onProposalCommentMail
     */
    public function setOnProposalCommentMail(bool $onProposalCommentMail)
    {
        $this->onProposalCommentMail = $onProposalCommentMail;
    }
}
