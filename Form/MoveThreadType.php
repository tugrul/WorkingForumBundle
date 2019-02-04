<?php

namespace Yosimitso\WorkingForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Yosimitso\WorkingForumBundle\Entity\{Thread,Subforum};

/**
 * Class MoveThreadType
 *
 * @package Yosimitso\WorkingForumBundle\Form
 */
class MoveThreadType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subforum', EntityType::class, [
                'class' => Subforum::class,
                'choice_label' => 'name',
                'multiple' => false,
                'label' => 'subforum.target',
                'translation_domain' => 'YosimitsoWorkingForumBundle',
                'group_by' => function ($item) {
                    return $item->getForum()->getName();
                }
            ])->add('save', SubmitType::class, [
                'label' => 'forum.confirm_move_thread',
                'translation_domain' => 'YosimitsoWorkingForumBundle'
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Thread::class
        ]);
    }
}