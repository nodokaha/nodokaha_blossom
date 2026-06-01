<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\AssetFile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Choice;

class AssetUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('asset', FileType::class, [
                'mapped' => false,
                'constraints' => [
                    new File(maxSize: '100M', mimeTypes: ['application/octet-stream']),
                ],
            ])
            ->add('encryptionKey', TextType::class, [
                'mapped' => false,
                'constraints' => [
                    new NotBlank(message: '暗号化キーを入力してください'),
                    new Length(min: 1, max: 255, minMessage: '暗号化キーを入力してください'),
                ],
                'attr' => [
                    'placeholder' => '例: your-encryption-key-here',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('assetType', ChoiceType::class, [
                'mapped' => false,
                'choices' => array_flip(AssetFile::ASSET_TYPE_LABELS),
                'placeholder' => '種別を選択してください',
                'constraints' => [
                    new NotBlank(message: '種別を選択してください'),
                    new Choice(choices: AssetFile::getAllowedAssetTypes(), message: '有効な種別を選択してください'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
