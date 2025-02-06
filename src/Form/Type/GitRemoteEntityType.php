<?php

namespace App\Form\Type;

use App\Entity\GitRemoteEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GitRemoteEntityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GitRemoteEntity::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', TextType::class, ['attr' => ['placeholder' => 'git@github.com:user/repo.git']])
            ->add('save', SubmitType::class, ['label' => 'Clone'])
        ;
    }
}
