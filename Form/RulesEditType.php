<?php

namespace Yosimitso\WorkingForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Yosimitso\WorkingForumBundle\Form\Type\MarkdownEditorType;

use Yosimitso\WorkingForumBundle\Entity\Rules;


/**
 * Class RulesEditType
 *
 * @package Yosimitso\WorkingForumBundle\Form
 */
class RulesEditType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lang', TextType::class, [
                'label' => 'admin.rules.lang'
            ])
            ->add('content',MarkdownEditorType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'admin.submit'
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Rules::class
        ]);
    }
}