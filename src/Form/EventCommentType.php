<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\EventComment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventCommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('authorName', TextType::class, [
                'label' => '名前',
                'help' => 'コメント欄に表示される名前です。',
                'attr' => [
                    'maxlength' => 80,
                    'placeholder' => '例：basis_user',
                    'autocomplete' => 'name',
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'コメント',
                'help' => '使い方の補足、導入時の気づき、改善案などを簡潔に投稿できます。',
                'attr' => [
                    'rows' => 5,
                    'maxlength' => 1200,
                    'placeholder' => '例：このWorldは夜設定のライト調整も合いそうです。',
                ],
            ])
            ->add('website', TextType::class, [
                'label' => 'Web site',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'bot-trap-field',
                    'autocomplete' => 'off',
                    'tabindex' => '-1',
                    'aria-hidden' => 'true',
                ],
                'row_attr' => [
                    'class' => 'bot-trap-row',
                    'aria-hidden' => 'true',
                ],
            ])
            ->add('submissionToken', HiddenType::class, [
                'mapped' => false,
                'data' => bin2hex(random_bytes(8)),
                'constraints' => [
                    new NotBlank(),
                    new Length(min: 16, max: 16),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventComment::class,
        ]);
    }
}
