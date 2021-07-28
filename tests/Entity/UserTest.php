<?php
namespace App\Tests\Entity;

use App\Entity\Credit;
use App\Entity\Deposit;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase {
	public function testName() {
		$user = new User();
		$user->setName('Aurimas');
		$this->assertEquals('Aurimas', $user->getName());
		$user->setName('aurimas');
		$this->assertNotEquals('aurimas', $user->getName());
		$this->assertEquals('Aurimas', $user->getName());
	}

	public function testSurname() {
		$user = new User();
		$user->setSurname('Rimkus');
		$this->assertEquals('Rimkus', $user->getSurname());
		$user->setSurname('rimkus');
		$this->assertNotEquals('rimkus', $user->getSurname());
		$this->assertEquals('Rimkus', $user->getSurname());
	}

	public function testPersonCode() {
		$user = new User();
		$user->setPersonCode(39711077447);
		$this->assertEquals(39711077447, $user->getPersonCode());
		$this->assertNotEquals(397110774477, $user->getPersonCode());
	}

	public function testComments() {
		$user = new User();
		$user->setComments('[2020-05-30 12:50] Tried contacting his phone, not picking up');
		$this->assertEquals('[2020-05-30 12:50] Tried contacting his phone, not picking up', $user->getComments());
		$this->assertNotEquals('Tried contacting his phone, not picking up', $user->getComments());
	}

	public function testAccountNumber() {
		$user = new User();
		$user->setAccountNumber('lt-601010012345678901');
		$this->assertEquals('LT601010012345678901', $user->getAccountNumber());
		$this->assertNotEquals('LT-601010012345678901 ', $user->getAccountNumber());
		$this->assertNotEquals('lt601010012345678901 ', $user->getAccountNumber());
		$this->assertNotEquals('lt-601010012345678901 ', $user->getAccountNumber());
	}

	public function testUserCredits() {
		$user = new User();
		$credit1 = new Credit();
		$deposit = new Deposit();
		$deposit->setCredit($credit1);
		$credit1->addDeposit($deposit);
		$credit1->setAmount(50);
		$this->assertEmpty($user->getCredits());
		$user->addCredit($credit1);
		$this->assertCount(1, $user->getCredits());
		$credit2 = new Credit();
		$credit2->setAmount(55);
		$user->addCredit($credit2);
		$this->assertCount(2, $user->getCredits());
		$user->addCredit($credit2);
		$this->assertCount(2, $user->getCredits());
		$user->removeCredit($credit2);
		$this->assertCount(1, $user->getCredits());
		$user->removeCredit($credit2);
		$this->assertCount(1, $user->getCredits());
		$user->removeCredit($credit1);
		$this->assertCount(0, $user->getCredits());
	}
}