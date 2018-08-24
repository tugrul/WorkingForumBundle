<?php


namespace Yosimitso\WorkingForumBundle\Util;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\TranslatorInterface;
use Yosimitso\WorkingForumBundle\Entity\Subscription as SubscriptionEntity;
use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Templating;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class Subscription
 *
 * @package Yosimitso\WorkingForumBundle\Util
 */
class Subscription
{
    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var Swift_Mailer
     */
    private $mailer;
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var string
     */
    private $siteTitle;
    /**
     * @var string
     */
    private $senderAddress;
    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * Subscription constructor.
     * @param EntityManager $em
     * @param Swift_Mailer $mailer
     * @param TranslatorInterface $translator
     * @param string $siteTitle
     * @param string $senderAddress
     * @param EngineInterface $templating
     */
    public function __construct(EntityManager $em, Swift_Mailer $mailer, TranslatorInterface $translator, string $siteTitle, string $senderAddress, EngineInterface $templating)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->siteTitle = $siteTitle;
        $this->senderAddress = $senderAddress;
        $this->templating = $templating;
        
    }

    /**
     * Notify subscribed users of a new post
     * @param $post
     * @return bool
     * @throws \Exception
     */
    public function notifySubscriptions($post)
    {
        $emailTranslation = $this->getEmailTranslation($post->getThread()->getSubforum(), $post->getThread(), $post, $post->getUser());
        $notifs = $this->em->getRepository('YosimitsoWorkingForumBundle:Subscription')->findByThread($post->getThread()->getId());

        if (!is_null($notifs)) {
            foreach ($notifs as $notif) {
                if (!empty($notif->getUser()->getEmailAddress())) {
                    $email = (new \Swift_Message())
                        ->setSubject($this->translator->trans('subscription.emailNotification.subject', $emailTranslation, 'YosimitsoWorkingForumBundle'))
                        ->setFrom($this->senderAddress)
                        ->setTo($notif->getUser()->getEmailAddress())
                        ->setBody(
                            $this->templating->render(
                                '@YosimitsoWorkingForum/Email/notification_new_message_en.html.twig',
                                ['user' => $notif->getUser(), 'thread' => $post->getThread(), 'post' => $post, 'postUser' => $post->getUser()]
                            ),
                            'text/html');

                    if (!$this->mailer->send($email)) {
                        throw new \Exception('email wasn\'t sent');
                    }
                }
            }
            return true;
        }
    }

    /**
     * @param $entity
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addSubscription($entity)
    {
        $subscription = new SubscriptionEntity($entity->getThread(), $entity->getUser());

        $this->em->persist($subscription);

        $this->em->flush();
    }

    /**
     * Get translated variable for email content
     * @param $subforum
     * @param $thread
     * @param $post
     * @param $user
     * @return array
     */
    private function getEmailTranslation($subforum, $thread, $post, $user)
    {
        return [
            'site.title' => $this->siteTitle,
            'subforum.name' => $subforum->getName(),
            'thread.label' => $thread->getLabel(),
            'thread.author' => $thread->getAuthor()->getUsername(),
        ];
    }
}