<?php

namespace App\Form\Type;

use App\Entity\GitCommit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GitCommitType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GitCommit::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => (new GitCommit())->getDefaultMessage()
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save'
            ])
        ;
    }
}
