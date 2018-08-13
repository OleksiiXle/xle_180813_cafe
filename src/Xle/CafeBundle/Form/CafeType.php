<?php

namespace Xle\CafeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CafeType extends AbstractType {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('googlePlaceId')
            ->add('title')->add('raiting')->add('review')
            ->add('address')->add('lat')
            ->add('lng')->add('statusTxt')->add('raitingTxt');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Xle\CafeBundle\Entity\Cafe'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(){
        return 'xle_cafebundle_cafe';
    }


}
