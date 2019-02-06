<?php


namespace Yosimitso\WorkingForumBundle\Twig\Extension;

use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Translation\TranslatorInterface;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use Yosimitso\WorkingForumBundle\Entity\UserInterface;

class HtmlUtilTwigExtension extends AbstractExtension
{
    /**
     * @var Packages
     */
    protected $packages;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $securityChecker;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var ParameterBagInterface
     */
    protected $parameterBag;

    public function __construct(Packages $packages, TranslatorInterface $translator,
                                AuthorizationCheckerInterface $securityChecker, TokenStorageInterface $tokenStorage,
                                ParameterBagInterface $parameterBag)
    {
        $this->packages = $packages;

        $this->translator = $translator;

        $this->securityChecker = $securityChecker;

        $this->tokenStorage = $tokenStorage;

        $this->parameterBag = $parameterBag;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('html_class_attribute', [$this, 'getHtmlClassAttribute'], ['is_safe' => ['html']]),
            new TwigFunction('user_avatar', [$this, 'getUserAvatar']),
            new TwigFunction('user_role', [$this, 'getUserRole']),
            new TwigFunction('wf_is_manager', [$this, 'isManager']),
            new TwigFunction('wf_is_admin', [$this, 'isAdmin']),
            new TwigFunction('wf_is_logged_in', [$this, 'isLoggedIn']),
            new TwigFunction('wf_is_user', [$this, 'isUser']),
            new TwigFunction('wf_parameter', [$this, 'getParameter'])
        ];
    }

    public function getHtmlClassAttribute(array $labels)
    {
        $labels = array_keys(array_filter($labels));
        return count($labels) > 0 ? ('class="' . implode(' ', $labels) . '"') : '';
    }

    public function getUserAvatar(UserInterface $user)
    {
        $avatarUrl = $user->getAvatarUrl();

        if (!empty($avatarUrl)) {
            return $this->packages->getUrl($avatarUrl, 'forum_avatar');
        }

        return $this->packages->getUrl('images/avatar/default.png', 'forum');
    }

    public function getUserRole(UserInterface $user)
    {
        $roles = $user->getRoles();

        $role = array_pop($roles);

        if (empty($role)) {
            $role = 'ROLE_USER';
        }

        return $this->translator->trans('forum.user.' . $role);
    }

    public function isAdmin()
    {
        return $this->isGranted(['ROLE_SUPER_ADMIN', 'ROLE_ADMIN']);
    }

    public function isModerator()
    {
        return $this->isGranted('ROLE_MODERATOR');
    }

    public function isManager()
    {
        return $this->isGranted(['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_MODERATOR']);
    }

    public function isLoggedIn()
    {
        return $this->isGranted('IS_AUTHENTICATED_REMEMBERED');
    }

    protected function isGranted($attributes)
    {
        try {
            return $this->securityChecker->isGranted($attributes);
        } catch (AuthenticationCredentialsNotFoundException $ex) {
            return false;
        }
    }

    public function isUser(UserInterface $user)
    {
        $token = $this->tokenStorage->getToken();

        if (is_null($token)) {
            return false;
        }

        /**
         * @var UserInterface
         */
        $sessionUser = $token->getUser();

        if (!is_object($sessionUser)) {
            return false;
        }

        return $user->getId() === $sessionUser->getId();
    }

    /**
     * @param $parameter
     *
     * @return mixed
     * @throws \Exception
     */
    public function getParameter($parameter)
    {
        $parameter = 'yosimitso_working_forum.' . $parameter;

        if ($this->parameterBag->has($parameter)) {
            return $this->parameterBag->get($parameter);
        }

        throw new \Exception(
            'The param "' . $parameter . '" is missing in the WorkingForumBundle configuration'
        );
    }

}