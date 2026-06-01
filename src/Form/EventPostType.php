<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\EventPost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
                'help' => 'BasisVRで共有するコンテンツ名を140文字以内で入力してください。',
                'attr' => [
                    'maxlength' => 140,
                    'placeholder' => '例：水辺のワールド向けランタンProp',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('contentType', ChoiceType::class, [
                'label' => 'コンテンツ種別',
                'help' => '投稿するBasisVRコンテンツの種別を選択してください。',
                'choices' => EventPost::contentTypeChoices(),
                'expanded' => true,
            ])
            ->add('authorName', TextType::class, [
                'label' => '投稿者名',
                'help' => '公開される名前です。個人情報は書きすぎないでください。',
                'attr' => [
                    'maxlength' => 80,
                    'placeholder' => '例：basis_creator',
                    'autocomplete' => 'name',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => '説明',
                'help' => '利用シーン、同梱物、導入手順、注意点などをまとめてください。',
                'attr' => [
                    'rows' => 10,
                    'maxlength' => 5000,
                    'placeholder' => "概要\n利用シーン\n同梱アセット\n導入手順\n注意点",
                ],
            ])
            ->add('relatedAssets', TextType::class, [
                'label' => '関連アセット',
                'required' => false,
                'help' => 'アセット名、ストレージキー、URLなどをカンマまたは改行区切りで入力してください。',
                'attr' => [
                    'placeholder' => '例：lantern.bee, thumbnail.png',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('tags', TextType::class, [
                'label' => 'タグ',
                'required' => false,
                'help' => '検索しやすいタグをカンマまたは改行区切りで入力してください。',
                'attr' => [
                    'placeholder' => '例：fantasy, night, beginner',
                    'autocomplete' => 'off',
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

        $listTransformer = new CallbackTransformer(
            static fn (?array $items): string => implode(', ', $items ?? []),
            static fn (?string $value): array => self::splitList((string) $value),
        );
        $builder->get('relatedAssets')->addModelTransformer($listTransformer);
        $builder->get('tags')->addModelTransformer($listTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventPost::class,
        ]);
    }

    /** @return list<string> */
    private static function splitList(string $value): array
    {
        $items = preg_split('/[\r\n,]+/', $value) ?: [];
        $normalized = [];
        foreach ($items as $item) {
            $item = trim($item);
            if ($item !== '' && ! in_array($item, $normalized, true)) {
                $normalized[] = $item;
            }
        }

        return $normalized;
    }
}
