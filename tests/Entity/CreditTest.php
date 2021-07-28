<?php

namespace App\Tests\Entity;

use _HumbugBox01d8f9a04075\Nette\Utils\DateTime;
use App\Entity\Credit;
use App\Entity\Deposit;
use App\Entity\Payment;
use App\Entity\User;
use App\Entity\Writeoff;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Date;

class CreditTest extends TestCase {

	public function testSetPaymentsSum() {
		$credit = new Credit();
		$credit->setPaymentsSum(50.74);
		$this->assertEquals(50.74, $credit->getPaymentsSum());
	}

	public function testLength() {
		$credit = new Credit();
		$credit->setLength(30);
		$this->assertEquals(30, $credit->getLength());
	}

	public function testDate() {
		$credit = new Credit();
		$date = new DateTime();
		$credit->setDate($date);
		$this->assertEquals($date, $credit->getDate());
	}

	public function testAcceptDate() {
		$credit = new Credit();
		$date = new DateTime();
		$credit->setAcceptDate($date);
		$this->assertEquals($date, $credit->getAcceptDate());

		$credit2 = new Credit();
		$this->assertNull($credit2->getAcceptDate());
	}

	public function testSetCustomerId() {
		$credit = new Credit();
		$user = new User();
		$credit->setCustomerId($user);
		$this->assertEquals($user, $credit->getCustomerId());
	}

	public function testGetId() {
		$credit = new Credit();
		$this->assertEquals(null, $credit->getId());
	}

	public function testDetalisation() {
		$credit = new Credit();
		$credit->setDetalisation(['array' => 'fictional array', 'fictional_int' => 54]);
		$this->assertTrue($credit->hasDetalisation());
		$this->assertEquals((object)['array' => 'fictional array', 'fictional_int' => 54], $credit->getDetalisation());
	}

	public function testPayments() {
		$credit = new Credit();
		$payment1 = new Payment();
		$paymentDate = new DateTime();
		$payment1->setCredit($credit);
		$payment1->setDate($paymentDate);
		$payment1->setIgnored(false);
		$payment1->setVerified(true);
		$payment1->setSum(74.15);
		$credit->addPayment($payment1);
		$this->assertEquals([$payment1], $credit->getActivePayments()->toArray());
		$this->assertEquals($paymentDate, $payment1->getDate());
		$this->assertNull($payment1->getId());

		$payment2 = new Payment();
		$payment2->setCredit($credit);
		$payment2->setDate(new DateTime());
		$payment2->setIgnored(true);
		$payment2->setVerified(false);
		$payment2->setSum(44);
		$credit->addPayment($payment2);
		$this->assertEquals([$payment1], $credit->getActivePayments()->toArray());

		$payment3 = new Payment();
		$payment3->setCredit($credit);
		$payment3->setDate(new DateTime());
		$payment3->setIgnored(true);
		$payment3->setVerified(true);
		$payment3->setSum(44);
		$credit->addPayment($payment3);
		$this->assertEquals([$payment1], $credit->getActivePayments()->toArray());

		$payment4 = new Payment();
		$payment4->setCredit($credit);
		$payment4->setDate(new DateTime());
		$payment4->setIgnored(false);
		$payment4->setVerified(false);
		$payment4->setSum(44);
		$credit->addPayment($payment4);
		$this->assertEquals([$payment1], $credit->getActivePayments()->toArray());
		$this->assertEquals([$payment1, $payment2, $payment3, $payment4], $credit->getAllPayments()->toArray());

		$payment5 = new Payment();
		$payment5->setCredit($credit);
		$payment5->setDate(new DateTime());
		$payment5->setIgnored(false);
		$payment5->setVerified(true);
		$payment5->setSum(46);
		$credit->addPayment($payment5);
		$this->assertContains($payment5, $credit->getActivePayments()->toArray());
		$this->assertNotContains($payment4, $credit->getActivePayments()->toArray());
		$this->assertEquals([$payment1, $payment2, $payment3, $payment4, $payment5], $credit->getAllPayments()->toArray());

		$credit->addPayment($payment5);
		$credit->addPayment($payment4);
		$this->assertContains($payment5, $credit->getActivePayments()->toArray());
		$this->assertNotContains($payment4, $credit->getActivePayments()->toArray());
		$this->assertCount(2, $credit->getActivePayments()->toArray());
		$this->assertEquals([$payment1, $payment2, $payment3, $payment4, $payment5], $credit->getAllPayments()->toArray());

		$credit->removePayment($payment1);
		$this->assertContains($payment5, $credit->getActivePayments()->toArray());
		$this->assertNotContains($payment1, $credit->getActivePayments()->toArray());
		$this->assertCount(1, $credit->getActivePayments()->toArray());

		$credit->removePayment($payment1);
		$credit->removePayment($payment2);
		$this->assertCount(1, $credit->getActivePayments()->toArray());
	}

	public function testPaymentsSum() {
		$credit = new Credit();
		$credit->setPaymentsSum(10);
		$this->assertEquals(10, $credit->getPaymentsSum());
		$credit->increasePaymentsSum(5);
		$this->assertEquals(15, $credit->getPaymentsSum());
	}

	public function testDebt() {
		$credit = new Credit();
		$credit->setAmount(500);
		$credit->setPrice(50);

		$payment = new Payment();
		$payment->setSum(17);
		$payment->setVerified(true);
		$payment->setIgnored(false);
		$credit->addPayment($payment);
		$credit->increasePaymentsSum($payment->getSum());

		$payment2 = new Payment();
		$payment2->setSum(5);
		$payment2->setVerified(false);
		$payment2->setIgnored(false);

		$writeoff = new Writeoff();
		$date = new DateTime();
		$writeoff->setCredit($credit);
		$writeoff->setAmount(3);
		$this->assertNull($writeoff->getReason());
		$this->assertNull($writeoff->getId());
		$this->assertNull($writeoff->getDate());
		$writeoff->setDate($date);
		$this->assertEquals($date, $writeoff->getDate());
		$writeoff->setReason('priezastis');
		$this->assertEquals('priezastis', $writeoff->getReason());
		$credit->addWriteoff($writeoff);

		$this->assertEquals([$writeoff], $credit->getWriteoffs()->toArray());

		$writeoff2 = new Writeoff();
		$writeoff2->setCredit($credit);
		$writeoff2->setAmount(4);
		$credit->addWriteoff($writeoff2);

		$this->assertEquals(526, $credit->getDebt());
		$credit->removeWriteoff($writeoff2);
		$this->assertEquals(530, $credit->getDebt());

		$credit->removePayment($payment);
		$credit->increasePaymentsSum(-1 * $payment->getSum());
		$this->assertEquals(547, $credit->getDebt());
	}

	public function testDelay() {
		$credit = new Credit();
		$credit->setDetalisation(json_decode(unserialize('s:437:"[{"date":"2020-03-02","month":1,"amount":325,"price":115.92,"payment":440.92,"debt":0,"delay":0},{"date":"2020-04-02","month":2,"amount":325,"price":115.92,"payment":440.92,"debt":391.84000000000003,"delay":33},{"date":"2020-05-02","month":3,"amount":325,"price":115.92,"payment":440.92,"debt":440.92,"delay":3},{"date":"2020-06-02","month":4,"amount":325,"price":115.91,"payment":440.90999999999997,"debt":440.90999999999997,"delay":0}]";')));
		$this->assertEquals(json_decode(unserialize('s:437:"[{"date":"2020-03-02","month":1,"amount":325,"price":115.92,"payment":440.92,"debt":0,"delay":0},{"date":"2020-04-02","month":2,"amount":325,"price":115.92,"payment":440.92,"debt":391.84000000000003,"delay":33},{"date":"2020-05-02","month":3,"amount":325,"price":115.92,"payment":440.92,"debt":440.92,"delay":3},{"date":"2020-06-02","month":4,"amount":325,"price":115.91,"payment":440.90999999999997,"debt":440.90999999999997,"delay":0}]";')), $credit->getDetalisation());
		$this->assertTrue($credit->hasDetalisation());
		$this->assertEquals(33, $credit->getDelay());

		$credit2 = new Credit();
		$credit2->setLength(1);
		$date = date_sub(new \DateTime(), new \DateInterval('P4M'));
		$credit2->setAcceptDate($date);
		$credit2->setAmount(1);
		$credit2->setLength(3);
		$this->assertGreaterThan(27, $credit2->getDelay()); // delay > 27 d nes prilausomai nuo dienu sk. menesyje. o turi but 1 men

	}

	public function testDeposits() {
		$credit = new Credit();
		$deposit = new Deposit();
		$deposit->setCredit($credit);
		$credit->addDeposit($deposit);

		$this->assertEquals([$deposit], $credit->getDeposits()->toArray());

		$credit->removeDeposit($deposit);
		$this->assertEmpty($credit->getDeposits()->toArray());
	}
}