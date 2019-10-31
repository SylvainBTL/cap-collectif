<?php

namespace Capco\AppBundle\Controller\Site;

use Capco\AppBundle\Repository\AbstractSSOConfigurationRepository;
use Capco\AppBundle\Toggle\Manager;
use Capco\UserBundle\OpenID\OpenIDReferrerResolver;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SSOController extends Controller
{
    protected $toggleManager;
    protected $ssoRepository;
    protected $referrerResolver;

    public function __construct(
        Manager $toggleManager,
        AbstractSSOConfigurationRepository $ssoRepository,
        OpenIDReferrerResolver $referrerResolver
    ) {
        $this->toggleManager = $toggleManager;
        $this->ssoRepository = $ssoRepository;
        $this->referrerResolver = $referrerResolver;
    }

    /**
     * @Route("/sso/switch-user", name="app_sso_switch_user")
     * @Template("CapcoAppBundle:Default:sso_switch_user.html.twig")
     */
    public function switchUserAction()
    {
        // : RedirectResponse|array
        $user = $this->getUser();

        if (!$user || !$this->toggleManager->isActive('disconnect_openid')) {
            return $this->redirect('/');
        }

        return [];
    }

    /**
     * @Route("/sso/profile", name="app_sso_profile")
     */
    public function profileAction(Request $request): RedirectResponse
    {
        $user = $this->getUser();

        if (
            !$user ||
            (!$this->toggleManager->isActive('login_openid') &&
                $this->toggleManager->isActive('profiles'))
        ) {
            return $this->redirect('/');
        }

        $ssoConfiguration = $this->ssoRepository->findOneBy(['enabled' => 1]);

        if (!$ssoConfiguration || !$ssoConfiguration->getProfileUrl()) {
            return $this->redirect('/');
        }

        $referrerParameter = $this->referrerResolver->getRefererParameterForProfile();

        return $this->redirect(
            $ssoConfiguration->getProfileUrl() .
                '?' .
                $referrerParameter .
                '=' .
                $request->query->get('referrer', $request->getBaseUrl())
        );
    }
}
