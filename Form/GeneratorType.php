<?php

namespace SBC\CodeMakerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GeneratorType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entities_name = (key_exists('entities_name', $options)) ? $options['entities_name'] : null;

        $builder
            ->add('displayName', ChoiceType::class, array(
                'placeholder' => '',
                'choices' => $entities_name,
                'required' => true,
//                'attr' => array(
//                    'class' => 'md-input label-fixed'
//                )
            ))
            ->add('simplePattern', TextType::class, array(
                'required' => true,
//                'attr' => array(
//                    'class' => 'md-input label-fixed',
//                    'style' => 'text-transform: initial'
//                )
            ))
            ->add('lastCode', TextType::class, array(
                'required' => true,
//                'attr' => array(
//                    'class' => 'md-input label-fixed',
//                    //'style' => 'text-transform: inherit'
//                )
            ))
            ->add('respectPattern', CheckboxType::class, array(
                'required' => false,
//                'attr' => array(
//                    'data-name' => 'FODEC_ROW',
//                    'checked' => '',
//                    'data-switchery' =>'',
//                    'data-switchery-size' => 'large'
//                )
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SBC\CodeMakerBundle\Entity\Generator'
        ));
        $resolver->setDefined('entities_name');
    }

}
