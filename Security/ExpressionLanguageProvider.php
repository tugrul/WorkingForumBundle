<?php

namespace Yosimitso\WorkingForumBundle\Security;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;


class ExpressionLanguageProvider implements ExpressionFunctionProviderInterface
{
    protected $allowAnonymousRead = false;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->allowAnonymousRead = $parameterBag->get('yosimitso_working_forum.allow_anonymous_read');
    }

    public function isAnonymousReadAllowed()
    {
        return $this->allowAnonymousRead;
    }

    public function getFunctions()
    {
        return [new ExpressionFunction(
            'is_anonymous_read_allowed',
            function() {
                return '$this->isAnonymousReadAllowed';
            }, [$this, 'isAnonymousReadAllowed'])
        ];
    }
}