<?php

namespace App\Tests\Form;

use App\Entity\Pricelist;
use Symfony\Component\Form\Test\TypeTestCase;

class PricelistTypeTest extends TypeTestCase {
	public function testSubmitValidData() {
		$formData = [
			'credit_from' => 1,
			'credit_to' => 1000,
			'length_from' => 1,
			'length_to' => 36,
			'interest' => 7,
		];

		$comparedObj = new Pricelist();
// $objectToCompare will retrieve data from the form submission; pass it as the second argument
		$form = $this->factory->create(\App\Form\PricelistType::class, $comparedObj);

		$pricelist = new Pricelist();
		$pricelist->setInterest($formData['interest']);
		$pricelist->setLengthTo($formData['length_to']);
		$pricelist->setLengthFrom($formData['length_from']);
		$pricelist->setCreditTo($formData['credit_to']);
		$pricelist->setCreditFrom($formData['credit_from']);

// submit the data to the form directly
		$form->submit($formData);

		$this->assertTrue($form->isSynchronized());

// check that $objectToCompare was modified as expected when the form was submitted
		$this->assertEquals($pricelist, $comparedObj);

		$view = $form->createView();
		$children = $view->children;

		foreach (array_keys($formData) as $key) {
			$this->assertArrayHasKey($key, $children);
		}
	}
}