<?php


namespace App\Form;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepositTypeType extends AbstractType implements FormTypeInterface
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('Name')
			->add('submit', SubmitType::class)
		;
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => \App\Entity\DepositType::class,
		]);
	}
}