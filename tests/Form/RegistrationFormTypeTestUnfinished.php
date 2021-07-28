<?php

namespace App\Tests\Form;

use App\Entity\Pricelist;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\Validation;

class RegistrationFormTypeTestUnfinished extends TypeTestCase {

	protected function setUp() : void
	{
		parent::setUp();

		$this->factory = Forms::createFormFactoryBuilder()
			->addTypeExtension(
				new FormTypeValidatorExtension(
					$this->createMock('Symfony\Component\Validator\Validator\ValidatorInterface')
				)
			)
			->addTypeGuesser(
				$this->getMockBuilder(
					'Symfony\Component\Form\Extension\Validator\ValidatorTypeGuesser'
				)
					->disableOriginalConstructor()
					->getMock()
			)
			->getFormFactory();

		$this->dispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
		$this->builder = new FormBuilder(null, null, $this->dispatcher, $this->factory);
	}

	public function testSubmitValidData() {
		$formData = [
			'email' => 'mei@las.lt',
			'name' => 'Vardenis',
			'surname' => 'Pavardauskas',
			'person_code' => 39711055874,
			'accountNumber' => 'LT155448874521',
		];

		$objectToCompare = new User();
// $objectToCompare will retrieve data from the form submission; pass it as the second argument
		$form = $this->factory->create(RegistrationFormType::class, $objectToCompare);

		$object = new User();
		$object->setEmail($formData['email']);
		$object->setName($formData['name']);
		$object->setSurname($formData['surname']);
		$object->setPersonCode($formData['person_code']);
		$object->setAccountNumber($formData['accountNumber']);

// submit the data to the form directly
		//$form->submit($formData);

		$this->assertTrue($form->isSynchronized());


		$view = $form->createView();
		$children = $view->children;

		var_dump($children);

		foreach (array_keys($formData) as $key) {
			$this->assertArrayHasKey($key, $children);
		}
	}
}