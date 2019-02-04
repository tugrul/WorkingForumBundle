<?php


namespace Yosimitso\WorkingForumBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Translation\TranslatorInterface;
use Yosimitso\WorkingForumBundle\Entity\Subscription;


/**
 * Class SubscriptionController
 * @package Yosimitso\WorkingForumBundle\Controller
 *
 * @Route("/thread")
 */
class SubscriptionController extends BaseController
{

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;

        $this->translator = $translator;
    }

    /**
     * @Route("/{threadId}/subscribe", name="subscribe_thread", options={"expose": true})
     * @IsGranted("ROLE_USER")
     *
     * @param string $threadId
     * @return Response
     */
    public function subscribeAction(string $threadId)
    {
        $subscription = new Subscription();
        $subscription->setUser($this->getUser());
        $subscription->setThread($this->getThreadById($threadId));

        try {
            $this->entityManager->persist($subscription);
            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            //TODO: specify exception type
            return $this->json(['success' => false, 'message' => $exception->getMessage()]);
        }

        return $this->json([
            'success' => true,
            'state' => [
                'content' => $this->translator->trans('forum.cancel_subscription'),
                'data' => [
                    'route' => 'workingforum_unsubscribe_thread'
                ]
            ]
        ]);
    }

    /**
     * @Route("/{threadId}/unsubscribe", name="unsubscribe_thread", options={"expose": true})
     * @IsGranted("ROLE_USER")
     *
     * @param string $threadId
     * @return Response
     */
    public function unsubscribeAction(string $threadId)
    {
        $subscription = $this->entityManager->getRepository(Subscription::class)
            ->findOneBy(['user' => $this->getUser(), 'thread' => $this->getThreadById($threadId)]);

        if (empty($subscription)) {
            return $this->json(['success' => false]);
        }

        $this->entityManager->remove($subscription);
        $this->entityManager->flush();


        return $this->json([
            'success' => true,
            'state' => [
                'content' => $this->translator->trans('forum.add_subscription'),
                'data' => [
                    'route' => 'workingforum_subscribe_thread'
                ]
            ]
        ]);
    }




}