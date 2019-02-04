<?php

namespace Yosimitso\WorkingForumBundle\Twig\Extension;

use Doctrine\ORM\EntityManagerInterface;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

use Symfony\Component\Translation\TranslatorInterface;
use Yosimitso\WorkingForumBundle\Entity\Post;
use Yosimitso\WorkingForumBundle\Entity\PostReportReview;

/**
 * Class QuoteTwigExtension
 *
 * @package Yosimitso\WorkingForumBundle\Twig\Extension
 */
class QuoteTwigExtension extends AbstractExtension
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface    $translator
     */
    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new TwigFilter('quote',[$this, 'quote']),
        ];
    }

    /**
     * @param $text
     *
     * @return mixed
     */
    public function quote($text)
    {
        return preg_replace_callback('#\[quote=([0-9]+)\]#',
            function ($listQuote) {

                /** @var Post $post */
                $post = $this->entityManager->getRepository(Post::class)
                    ->find($listQuote[1]);

                if (empty($post)) {
                    return '';
                }

                $reviews = $post->getReviews();

                if ($reviews->count() === 0) {
                    $content = $post->getContent();
                } else {
                    $content = implode(PHP_EOL, $reviews->map(function(PostReportReview $review){
                        return '**moderator**: ' . $review->getReviewer()->getUsername() .
                               '**, reason: ' . $review->getReason();
                    })->toArray());
                }

                return implode(PHP_EOL, [
                    '> **' . $post->getUser()->getUsername() . ' ' . $this->translator->trans('forum.has_written',
                        [], 'YosimitsoWorkingForumBundle') . ':**',
                    '> ',
                    $this->markdownQuote($this->quote($content))
                ]);
            },
            $text
        );
    }

    private function markdownQuote($text) {
        return '> ' . preg_replace('/\n/', "\n> ", $text);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'quote';
    }
}