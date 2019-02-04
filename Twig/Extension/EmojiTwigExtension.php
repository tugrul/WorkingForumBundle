<?php

namespace Yosimitso\WorkingForumBundle\Twig\Extension;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Asset\Packages as AssetPackages;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;

/**
 * Class EmojiTwigExtension
 *
 * @package Yosimitso\WorkingForumBundle\Twig\Extension
 */
class EmojiTwigExtension extends AbstractExtension
{
    /**
     * @var
     */
    protected $asset;

    /**
     * @var array
     */
    protected static $emojis = [
        ':smile:' => 'smile.png', ':wink:' => 'wink.png', ':angry:' => 'angry.png', ':biggrin:' => 'biggrin.png',
        ':crying:' => 'crying.png', ':frown:' => 'frown.png', ':tongue:' => 'tongue.png', ':yawn:' => 'yawn.png',
        ':zipped:' => 'zipped.png', ':sick:' => 'sick.png', ':whistle:' => 'whistle.png', ':evil:' => 'evil.png',
        ':stress:' => 'stress.png', ':delicious:' => 'delicious.png', ':bashful:' => 'bashful.png',
        ':bored:' => 'bored.png', ':confused:' => 'confused.png', ':heart:' => 'heart.png', ':love:' => 'love.png',
        ':oh:' => 'oh.png', ':nerdy:' => 'nerdy.png', ':present:' => 'present.png', ':sun:' => 'sun.png',
        ':sunglasses:' => 'sunglasses.png', ':xd:' => 'xd.png', ':football:' => 'football.png', ':tennis:' => 'tennis.png',
        ':basketball:' => 'basketball.png', ':thumbup:' => 'thumbup.png', ':thumbdown:' => 'thumbdown.png'
    ];

    /**
     * EmojiTwigExtension constructor.
     *
     * @param $asset
     */
    public function __construct(AssetPackages $asset)
    {
        $this->asset = $asset;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new TwigFilter('wf_emoji', [$this, 'filterEmoji'])
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('wf_emoji_list', [$this, 'listEmoji'])
        ];
    }

    /**
     * @param $text
     *
     * @return mixed
     */
    public function filterEmoji($text)
    {
        return preg_replace_callback('/:\w+:/', function($matches) {

            if (!isset(self::$emojis[$matches[0]])) {
                return $matches[0];
            }

            return '<img src="' . $this->asset->getUrl(self::$emojis[$matches[0]],
                    'forum_emojis') . '" />';

        }, $text);
    }

    public function listEmoji()
    {
        $emojis = self::$emojis;

        array_walk($emojis, function(&$value, $key) {
            $value = '<li><img data-key="' . $key . '" src="' . $this->asset->getUrl($value,
                    'forum_emojis') . '" /></li>';
        });

        return '<ul>' . implode('', $emojis) . '</ul>';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'wf_emoji';
    }
}