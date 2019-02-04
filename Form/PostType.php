<?php

namespace Yosimitso\WorkingForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

use Yosimitso\WorkingForumBundle\Form\Type\FileCollectionType;
use Yosimitso\WorkingForumBundle\Form\Type\MarkdownEditorType;

// use Yosimitso\WorkingForumBundle\Entity\Post;

/**
 * Class PostType
 *
 * @package Yosimitso\WorkingForumBundle\Form
 */
class PostType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('content', MarkdownEditorType::class, [
            'translation_domain' => 'YosimitsoWorkingForumBundle',
            'label' => 'forum.content',
            'container_id' => 'wf-post-content'
        ]);

        if ($options['canUploadFiles']) {
            $builder->add('files', FileCollectionType::class, [
                'required' => false,
                'label' => 'forum.enclosed_files',
                'entry_options' => [
                    'label' => 'forum.file'
                ]
            ]);
        }

        if ($options['canSubscribeThread']) {
            $builder->add('subscribe', CheckboxType::class, [
                'translation_domain' => 'YosimitsoWorkingForumBundle',
                'label' => 'forum.subscribe',
                'required' => false
            ]);
        }

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'canSubscribeThread',
            'canUploadFiles'
        ]);
    }
}