<?php

namespace Capco\AppBundle\Controller\Api;

use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Entity\ProposalComment;
use Capco\AppBundle\Entity\ProposalSelectionVote;
use Capco\AppBundle\Entity\Steps\SelectionStep;
use Capco\AppBundle\Entity\Selection;
use Capco\AppBundle\Form\ProposalSelectionVoteType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Capco\AppBundle\CapcoAppBundleEvents;
use Capco\AppBundle\Event\CommentChangedEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class SelectionStepsController extends FOSRestController
{
    /**
    * @Get("/selection_steps/{selection_step_id}")
    * @ParamConverter("selectionStep", options={"mapping": {"selection_step_id": "id"}})
    * @View(statusCode=200, serializerGroups={"Statuses", "Steps", "SelectionSteps", "VoteThreshold"})
    */
   public function getBySelectionStepAction(SelectionStep $selectionStep)
   {
       return $selectionStep;
   }

    /**
     * @Post("/selection_steps/{selection_step_id}/proposals/search")
     * @ParamConverter("selectionStep", options={"mapping": {"selection_step_id": "id"}})
     * @QueryParam(name="page", requirements="[0-9.]+", default="1")
     * @QueryParam(name="pagination", requirements="[0-9.]+", default="100")
     * @QueryParam(name="order", requirements="(old|last|votes|comments|random)", nullable=true)
     * @View(statusCode=200, serializerGroups={"Proposals", "UsersInfos", "UserMedias"})
     */
    public function getProposalsBySelectionStepAction(Request $request, SelectionStep $selectionStep, ParamFetcherInterface $paramFetcher)
    {
        $page = (int) $paramFetcher->get('page');
        $pagination = (int) $paramFetcher->get('pagination');
        $order = $paramFetcher->get('order') ?: $selectionStep->getDefaultSort();

        if ($order === 'votes' && !$selectionStep->isVotable()) {
            $order = 'last';
        }

        $terms = $request->request->has('terms') ? $request->request->get('terms') : null;

        // Filters
        $providedFilters = $request->request->has('filters') ? $request->request->get('filters') : [];
        $providedFilters['selectionStep'] = $selectionStep->getId();
        $providedFilters['step'] = $selectionStep->getId();
        if (array_key_exists('statuses', $providedFilters)) {
            $providedFilters['selectionStatuses'] = $providedFilters['statuses'];
            unset($providedFilters['statuses']);
        }

        $results = $this->get('capco.search.resolver')->searchProposals(
            $page,
            $pagination,
            $order,
            $terms,
            $providedFilters
        );

        return $results;
    }

    /**
     * @Post("/selection_steps/{selectionStepId}/selections")
     * @ParamConverter("selectionStep", options={"mapping": {"selectionStepId": "id"}})
     * @Security("has_role('ROLE_ADMIN')")
     * @View(statusCode=201, serializerGroups={})
     */
    public function selectProposalAction(Request $request, SelectionStep $selectionStep)
    {
        $em = $this->getDoctrine()->getManager();
        $proposal = $em->getRepository('CapcoAppBundle:Proposal')->find($request->request->get('proposal'));

        $selection = new Selection();
        $selection->setSelectionStep($selectionStep);
        $proposal->addSelection($selection);
        $em->persist($selection);
        $em->flush();
    }

    /**
     * @Delete("/selection_steps/{selectionStepId}/selections/{proposalId}")
     * @ParamConverter("selectionStep", options={"mapping": {"selectionStepId": "id"}})
     * @ParamConverter("proposal", options={"mapping": {"proposalId": "id"}})
     * @Security("has_role('ROLE_ADMIN')")
     * @View(statusCode=204, serializerGroups={})
     */
    public function unselectProposalAction(SelectionStep $selectionStep, Proposal $proposal)
    {
        $em = $this->getDoctrine()->getManager();
        $selection = $em->getRepository('CapcoAppBundle:Selection')
                        ->findOneBy(['proposal' => $proposal, 'selectionStep' => $selectionStep]);
        if (!$selection) {
            throw new \InvalidArgumentException('Error Processing Request', 1);
        }

        $em->remove($selection);
        $em->flush();
    }

    /**
     * @Patch("/selection_steps/{selectionStepId}/selections/{proposalId}")
     * @ParamConverter("selectionStep", options={"mapping": {"selectionStepId": "id"}})
     * @ParamConverter("proposal", options={"mapping": {"proposalId": "id"}})
     * @Security("has_role('ROLE_ADMIN')")
     * @View(statusCode=200, serializerGroups={"Statuses"})
     */
    public function updateSelectionStatusAction(Request $request, SelectionStep $selectionStep, Proposal $proposal)
    {
        $em = $this->getDoctrine()->getManager();
        $selection = $em->getRepository('CapcoAppBundle:Selection')->findOneBy([
          'proposal' => $proposal,
          'selectionStep' => $selectionStep,
        ]);
        if (!$selection) {
            throw new \InvalidArgumentException('Error Processing Request', 1);
        }

        $status = null;
        if ($request->request->get('status')) {
            $status = $em->getRepository('CapcoAppBundle:Status')->find($request->request->get('status'));
        }

        $selection->setStatus($status);
        $em->flush();

        return $status;
    }

    /**
     * @Post("/selection_steps/{selection_step_id}/proposals/{proposal_id}/votes")
     * @ParamConverter("selectionStep", options={"mapping": {"selection_step_id": "id"}})
     * @ParamConverter("proposal", options={"mapping": {"proposal_id": "id"}})
     * @View(statusCode=200, serializerGroups={"ProposalSelectionVotes", "UsersInfos", "UserMedias"})
     */
    public function voteOnProposalAction(Request $request, SelectionStep $selectionStep, Proposal $proposal)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        // Check if we can vote without account
        if (!$this->get('capco.toggle.manager')->isActive('vote_without_account') && !$user) {
            throw new BadRequestHttpException('You cannot vote without an account.');
        }

        // Check if proposal is in step
        if (!in_array($selectionStep, $proposal->getSelectionSteps())) {
            throw new BadRequestHttpException('This proposal is not associated to this selection step.');
        }

        // Check if selection step is contributable
        if (!$selectionStep->canContribute()) {
            throw new BadRequestHttpException('This selection step is no longer contributable.');
        }

        // Check if selection step is votable
        if (!$selectionStep->isVotable()) {
            throw new BadRequestHttpException('This selection step is not votable.');
        }

        // Check if user has reached limit of votes
        if ($selectionStep->isNumberOfVotesLimitted()) {
            $countUserVotes = $em
                ->getRepository('CapcoAppBundle:ProposalSelectionVote')
                ->countVotesByStepAndUser($selectionStep, $user)
            ;
            if ($countUserVotes >= $selectionStep->getVotesLimit()) {
                throw new BadRequestHttpException('You have reached the limit of votes.');
            }
        }

        // If selection step vote type is of type "budget", user must be logged in
        if (!$user && $selectionStep->isBudgetVotable()) {
            throw new UnauthorizedHttpException('Must be logged to vote.');
        }

        $vote = (new ProposalSelectionVote())
            ->setIpAddress($request->getClientIp())
            ->setUser($user)
            ->setProposal($proposal)
            ->setSelectionStep($selectionStep)
        ;

        $form = $this->createForm(ProposalSelectionVoteType::class, $vote);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $form;
        }

        if ($form->has('comment') && !empty($content = $form->get('comment')->getData())) {
            $comment = new ProposalComment();
            $comment
                ->setAuthor($vote->getUser())
                ->setAuthorName($vote->getUsername())
                ->setAuthorEmail($vote->getEmail())
                ->setBody($content)
                ->setProposal($proposal)
            ;

            $em->persist($comment);
            $this->get('event_dispatcher')->dispatch(
                CapcoAppBundleEvents::COMMENT_CHANGED,
                new CommentChangedEvent($comment, 'add')
            );
        }

        $em->persist($vote);
        $em->flush();

        $this
          ->get('fos_elastica.object_persister.app.proposal')
          ->insertOne($proposal)
        ;

        return $vote;
    }

    /**
     * @Security("has_role('ROLE_USER')")
     * @Delete("/selection_steps/{selection_step_id}/proposals/{proposal_id}/votes")
     * @ParamConverter("selectionStep", options={"mapping": {"selection_step_id": "id"}})
     * @ParamConverter("proposal", options={"mapping": {"proposal_id": "id"}})
     * @View(statusCode=200, serializerGroups={"ProposalSelectionVotes", "UsersInfos", "UserMedias"})
     */
    public function deleteVoteOnProposalAction(SelectionStep $selectionStep, Proposal $proposal)
    {
        $em = $this->getDoctrine()->getManager();

        // Check if proposal is in step
        if (!in_array($selectionStep, $proposal->getSelectionSteps(), true)) {
            throw new BadRequestHttpException('This proposal is not associated to this selection step.');
        }

        // Check if selection step is contributable
        if (!$selectionStep->canContribute()) {
            throw new BadRequestHttpException('This selection step is no longer contributable.');
        }

        $vote = $em
            ->getRepository('CapcoAppBundle:ProposalSelectionVote')
            ->findOneBy([
                'user' => $this->getUser(),
                'proposal' => $proposal,
                'selectionStep' => $selectionStep,
            ]);

        if (!$vote) {
            throw new BadRequestHttpException('You have not voted for this proposal in this selection step.');
        }

        $em->remove($vote);
        $em->flush();

        $this
          ->get('fos_elastica.object_persister.app.proposal')
          ->insertOne($proposal)
        ;

        return $vote;
    }

    /**
     * @Post("/selection_step/{selection_step_id}/proposals/{proposal_id}/notify-status-changed")
     * @ParamConverter("selectionStep", options={"mapping": {"selection_step_id": "id"}})
     * @ParamConverter("proposal", options={"mapping": {"proposal_id": "id"}})
     * @Security("has_role('ROLE_ADMIN')")
     * @View(statusCode=200)
     */
    public function notifyProposalStatusChangeInSelectionAction(SelectionStep $selectionStep, Proposal $proposal)
    {
        $selection = $this->getDoctrine()
            ->getManager()
            ->getRepository('CapcoAppBundle:Selection')
            ->findOneBy([
                'proposal' => $proposal,
                'selectionStep' => $selectionStep,
            ]);

        if (!$selection || !$selection->getStatus()) {
            throw new BadRequestHttpException('Proposal should have a status');
        }

        $this->container->get('capco.notify_manager')->notifyProposalStatusChangeInSelection($selection);
    }
}
