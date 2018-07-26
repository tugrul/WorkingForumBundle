<?php


namespace Yosimitso\WorkingForumBundle\Util;

use Yosimitso\WorkingForumBundle\Entity\Subscription as SubscriptionEntity;

/**
 * Class Subscription
 *
 * @package Yosimitso\WorkingForumBundle\Util
 */
class Subscription
{
    private $em;
    private $mailer;
    private $translator;
    private $siteTitle;
    private $swiftMailerParameters;
    private $senderAddress;
    private $templating;

    public function __construct($em, $mailer, $translator, $siteTitle, $senderAddress, $templating)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->siteTitle = $siteTitle;
        $this->senderAddress = $senderAddress;
        $this->templating = $templating;
        
    }

    public function notifySubscriptions($post)
    {
        $emailTranslation = $this->getEmailTranslation($post->getThread()->getSubforum(), $post->getThread(), $post, $post->getUser());
        $notifs = $this->em->getRepository('YosimitsoWorkingForumBundle:Subscription')->findByThread($post->getThread()->getId());

        if (!is_null($notifs)) {
            foreach ($notifs as $notif) {
                if (!empty($notif->getUser()->getEmailAddress())) {
                    $email = (new \Swift_Message())
                        ->setSubject($this->translator->trans('subscription.emailNotification.subject', $emailTranslation))
                        ->setFrom($this->senderAddress)
                        ->setTo($notif->getUser()->getEmailAddress())
                        ->setBody(
                            $this->templating->render(
                                '@YosimitsoWorkingForum/Email/notification_new_message_en.html.twig', ['user' => $notif->getUser(), 'thread' => $post->getThread(), 'post' => $post, 'postUser' => $post->getUser()]
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

    public function addSubscription($entity)
    {
        $subscription = new SubscriptionEntity($entity->getThread(), $entity->getUser());

        $this->em->persist($subscription);

        $this->em->flush();
    }

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