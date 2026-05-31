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
                'help' => '見直し内容が分かる要点を140文字以内で入力してください。',
                'attr' => [
                    'maxlength' => 140,
                    'placeholder' => '例：検索体験の見直しと採用判断',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('authorName', TextType::class, [
                'label' => 'レビュアー名',
                'help' => '公開される名前です。個人情報は書きすぎないでください。',
                'attr' => [
                    'maxlength' => 80,
                    'placeholder' => '例：reviewer',
                    'autocomplete' => 'name',
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => '本文',
                'help' => '変更理由、評価、懸念、次のアクションを含めると判断を追いやすくなります。',
                'attr' => [
                    'rows' => 10,
                    'maxlength' => 5000,
                    'placeholder' => "対象\n見直した理由\n良かった点\n懸念\n次のアクション",
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
