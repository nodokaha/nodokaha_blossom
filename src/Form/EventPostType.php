<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\EventPost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'タイトル',
                'help' => '一覧で読みたくなる要点を140文字以内で入力してください。',
                'attr' => [
                    'maxlength' => 140,
                    'placeholder' => '例：6月 BasisVR ワールドツアー開催',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('authorName', TextType::class, [
                'label' => '投稿者名',
                'help' => '公開される名前です。個人情報は書きすぎないでください。',
                'attr' => [
                    'maxlength' => 80,
                    'placeholder' => '例：BasisVR運営',
                    'autocomplete' => 'name',
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => '本文',
                'help' => '日時・参加方法・注意事項を含めると参加者が迷いません。',
                'attr' => [
                    'rows' => 10,
                    'maxlength' => 5000,
                    'placeholder' => "イベント概要\n開催日時\n参加方法\n注意事項",
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
            'data_class' => EventPost::class,
        ]);
    }
}
