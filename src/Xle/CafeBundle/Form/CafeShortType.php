<?php

namespace Xle\CafeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CafeShortType extends AbstractType {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('raiting', ChoiceType::class, [
                'choices' => [
                    'Не установлен' => 0,
                    'Ни какое' => 1,
                    'Ниже среднего' => 2,
                    'Так-себе' => 3,
                    'Очень даже ничего' => 4,
                    'Фэн-шуй' => 5,
                ],
                'label' => 'Рейтинг'
            ])
            ->add('review', TextareaType::class,[
                'attr' => ['row' => 2, 'col' => 20, ],
                'label' => 'Отзыв'
            ])
            ->add('id', IntegerType::class, [
                'attr' => ['style' => 'display:none;'],
                'label' => false

            ]);
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
    public function getBlockPrefix() {
        return 'xle_cafebundle_cafe';
    }


}
