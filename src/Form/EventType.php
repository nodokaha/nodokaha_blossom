<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'イベントタイトル',
                'constraints' => [new NotBlank()],
            ])
            ->add('description', TextareaType::class, [
                'label' => '説明',
                'required' => false,
            ])
            ->add('location', TextType::class, [
                'label' => '場所',
                'required' => false,
            ])
            ->add('startAt', DateTimeType::class, [
                'label' => '開始日時',
                'widget' => 'single_text',
            ])
            ->add('endAt', DateTimeType::class, [
                'label' => '終了日時',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('allDay', CheckboxType::class, [
                'label' => '終日',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
