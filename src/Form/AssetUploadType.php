<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class AssetUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('asset', FileType::class, [
                'mapped' => false,
                'constraints' => [
                    new File(maxSize: '20M', mimeTypes: ['image/*', 'model/gltf-binary', 'application/octet-stream']),
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
