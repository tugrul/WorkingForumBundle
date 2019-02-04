<?php

namespace Yosimitso\WorkingForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Yosimitso\WorkingForumBundle\Entity\Forum;

/**
 * Class AdminForumType
 *
 * @package Yosimitso\WorkingForumBundle\Form
 */
class AdminForumType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'admin.forum_name',
                'translation_domain' => 'YosimitsoWorkingForumBundle'
            ])
            ->add('description', TextType::class, [
                'label' => 'admin.forum_description',
                'translation_domain' => 'YosimitsoWorkingForumBundle'
            ])
            ->add('slug', TextType::class, [
                'required' => false,
                'label' => 'admin.forum_slug',
                'translation_domain' => 'YosimitsoWorkingForumBundle'
            ])
            ->add('subforums', CollectionType::class, [
                'entry_type' => AdminSubforumType::class,
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true
            ])
            ->add('submit',SubmitType::class, [
                'label' => 'admin.submit',
                'translation_domain' => 'YosimitsoWorkingForumBundle',
                'attr' => ['class' => 'wf_button']
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Forum::class
        ]);
    }
}
