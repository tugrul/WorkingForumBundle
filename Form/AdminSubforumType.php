<?php

namespace Yosimitso\WorkingForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\CallbackTransformer;

use Yosimitso\WorkingForumBundle\Entity\Subforum;

class AdminSubforumType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',TextType::class, [
                'error_bubbling' => true,
                'attr' => ['class' => 'form_subforum']
            ])
            ->add('slug', TextType::class, [
                'required' => false,
                'error_bubbling' => true
            ])
            ->add('allowedRoles',TextType::class, [
                'error_bubbling' => true,
                'required' => false,
                'translation_domain' => 'YosimitsoWorkingForumBundle',
                'attr' => ['placeholder' => 'admin.empty_means_all']
            ]);


        $builder
            ->get('allowedRoles')
            ->addModelTransformer(new CallbackTransformer (
                function ($rolesAsArray) {
                    return empty($rolesAsArray) || !is_array($rolesAsArray) ?
                        '' : implode(', ', $rolesAsArray);
                },
                function ($rolesAsString) {
                    return array_filter(array_map('trim',
                        explode(',', $rolesAsString)));
                }
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Subforum::class,
        ]);
    }
}
