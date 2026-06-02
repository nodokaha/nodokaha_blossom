<?php

namespace App\Form;

use App\Entity\Asset;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class AssetType extends AbstractType
{
    public const TYPES = [
        'ワールド' => 'world',
        'アセット' => 'asset',
        'プロップ' => 'prop',
    ];

    public static function getTypeChoices(): array
    {
        return self::TYPES;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'アセット名',
                'constraints' => [new NotBlank()],
            ])
            ->add('description', TextareaType::class, [
                'label' => '説明',
                'required' => false,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'タイプ',
                'choices' => self::getTypeChoices(),
                'placeholder' => 'タイプを選択',
                'constraints' => [new NotBlank()],
            ])
            ->add('uploadedFile', FileType::class, [
                'label' => 'ファイル',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new File([
                        'maxSize' => '100M',
                        'mimeTypes' => [
                            'application/zip',
                            'application/x-rar-compressed',
                            'application/x-7z-compressed',
                            'image/png',
                            'image/jpeg',
                            'image/gif',
                            'application/octet-stream',
                        ],
                        'mimeTypesMessage' => '許可されたファイル形式は ZIP / RAR / 7Z / PNG / JPEG / GIF / octet-stream です。',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Asset::class,
        ]);
    }
}
