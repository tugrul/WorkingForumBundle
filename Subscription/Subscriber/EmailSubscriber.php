<?php


namespace Yosimitso\WorkingForumBundle\Subscription\Subscriber;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Yosimitso\WorkingForumBundle\Subscription\SubscriberInterface;
use Yosimitso\WorkingForumBundle\Entity\Post;
use Yosimitso\WorkingForumBundle\Entity\Subscription;


class EmailSubscriber implements SubscriberInterface
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * Subscription constructor.
     * @param \Swift_Mailer $mailer
     * @param TranslatorInterface $translator
     * @param ParameterBagInterface $parameterBag
     * @param EngineInterface $templating
     */
    public function __construct(\Swift_Mailer $mailer, TranslatorInterface $translator,
                                ParameterBagInterface $parameterBag, EngineInterface $templating)
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->parameterBag = $parameterBag;
        $this->templating = $templating;

    }

    public function notify(Subscription $subscription, Post $post)
    {
        $receiver = $subscription->getUser()->getEmail();

        if (empty($receiver)) {
            return;
        }

        $params = [
            'siteTitle' => $this->parameterBag->get('yosimitso_working_forum.site_title'),
            'subforumName' => $thread->getSubforum()->getName(),
            'threadLabel' => $thread->getLabel(),
            'threadAuthor' => $thread->getAuthor()->getUsername(),
        ];

        $body = $this->templating->render('@YosimitsoWorkingForum/Email/notification_new_message_en.html.twig', [
            'subscription' => $subscription,
            'post' => $post
        ]);

        $message = new \Swift_Message();

        $message->setSubject($this->translator->trans('subscription.emailNotification.subject',
            $params, 'YosimitsoWorkingForumBundle'));

        $message->setFrom($this->parameterBag->get('swiftmailer.sender_address'));
        $message->setTo($receiver);
        $message->setBody($body, 'text/html');

        $this->mailer->send($message);

    }

}