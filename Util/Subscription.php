<?php


namespace Yosimitso\WorkingForumBundle\Util;

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
    private $bundleParameters;
    private $swiftMailerParameters;

    public function __construct($em, $mailer, $translator, $bundleParameters, $swiftMailerParameters)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->bundleParameters = $bundleParameters;
        $this->swiftMailerParameters = $swiftMailerParameters;
    }

    public function notifySubscriptions($post)
    {
        $emailTranslation = $this->getEmailTranslation($post->getThread()->getSubforum(), $post->getThread(), $post, $post->getUser());
        $notifs = $this->em->getRepository('YosimitsoWorkingForum:Subscriptions')->findByThread($post->getThread()->getId());

        if (!is_null($notifs)) {
            foreach ($notifs as $notif) {
                $email = (new \Swift_Message())
                    ->setSubject($this->translator->trans('subscription.emailNotification.subject', $emailTranslation))
                    ->setFrom($this->swiftMailerParameters['sender_address'])
                    ->setTo($notif->getUser()->getEmailAddress())
                    ->setBody(
                        $this->templating->renderView(
                            'Email/notification_new_message_en.html.twig', ['user' => $notif->getUser(), 'thread' => $post->getThread(), 'post' => $post, 'postUser' => $post->getUser()]
                        ),
                        'text/html');

                if (!$this->mailer->send($email)) {
                    throw new \Exception('email wasn\'t sent');
                }
            }
            return true;
        }
    }

    private function getEmailTranslation($subforum, $thread, $post, $user)
    {
        return [
            'site.title' => $this->bundleParameters['site_title'],
            'subforum.name' => $subforum->getName(),
            'thread.label' => $thread->getLabel(),
            'thread.author' => $thread->getAuthor()->getUsername(),



        ]
    }
}