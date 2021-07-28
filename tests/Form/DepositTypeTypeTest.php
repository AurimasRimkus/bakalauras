<?php

namespace App\Tests\Form;

use App\Form\DepositTypeType;
use Symfony\Component\Form\Test\TypeTestCase;

class DepositTypeTypeTest extends TypeTestCase {
	public function testSubmitValidData() {
		$depositTypeInfo = [
			'Name' => 'NT',
		];

		$objectToCompare = new \App\Entity\DepositType();
// $objectToCompare will retrieve data from the form submission; pass it as the second argument
		$form = $this->factory->create(DepositTypeType::class, $objectToCompare);

		$depositType = new \App\Entity\DepositType();
		$depositType->setName($depositTypeInfo['Name']);


// submit the data to the form directly
		$form->submit($depositTypeInfo);

		$this->assertTrue($form->isSynchronized());

// check that $objectToCompare was modified as expected when the form was submitted
		$this->assertEquals($depositType, $objectToCompare);

		$view = $form->createView();
		$children = $view->children;

		foreach (array_keys($depositTypeInfo) as $key) {
			$this->assertArrayHasKey($key, $children);
		}
	}
}