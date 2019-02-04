<?php

namespace Yosimitso\WorkingForumBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MarkdownEditorType extends AbstractType
{
    public function getParent()
    {
        return TextareaType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($options['container_id'])) {
            $view->vars['container_id'] = $options['container_id'];
        }

        parent::buildView($view, $form, $options);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('container_id');
        $resolver->setAllowedTypes('container_id', 'string');
    }




}