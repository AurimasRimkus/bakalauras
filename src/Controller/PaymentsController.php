<?php
namespace App\Controller;

use App\Entity\Credit;
use App\Entity\Payment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PaymentsController extends AbstractController
{
	/**
	 * @Route("/payments", name="payments")
	 */
	public function paymentsIndex() {
		$em = $this->getDoctrine()->getManager()->getRepository(Payment::class);
		if($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPER_ADMIN')) {
			$payments = $em->findAll();
			return $this->render('payments.html.twig', ['payments' => $payments]);
		}
		return new RedirectResponse('/admin');
	}

	/**
	 * @Route("/changeIgnoredState/{id}", name="changeIgnoredState", methods={"GET", "HEAD"})
	 */
	public function changeIgnoredState(Request $request, int $id) {
		if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
			return new RedirectResponse('/admin');
		}
		$em = $this->getDoctrine()->getManager();
		$customerRepo = $em->getRepository(Payment::class);
		/**
		 * @var Payment $payment
		 */
		$payment = $customerRepo->find($id);
		if (empty($payment)) {
			return new RedirectResponse('/payments');
		}
		$payment->setIgnored(!$payment->getIgnored());
		$em->persist($payment);
		$em->flush();
		return new RedirectResponse('/payments');
	}

	/**
	 * @Route("/changeVerifiedState/{id}", name="changeVerifiedState", methods={"GET", "HEAD"})
	 */
	public function changeVerifiedState(Request $request, int $id) {
		if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
			return new RedirectResponse('/admin');
		}
		$em = $this->getDoctrine()->getManager();
		$customerRepo = $em->getRepository(Payment::class);
		/**
		 * @var Payment $payment
		 */
		$payment = $customerRepo->find($id);
		if (empty($payment)) {
			return new RedirectResponse('/payments');
		}
		$payment->setVerified(!$payment->getVerified());
		$em->persist($payment);
		$em->flush();
		return new RedirectResponse('/payments');
	}

	/**
	 * @Route("/addPayment", name="addPayment", methods={"GET", "HEAD"})
	 */
	public function AddPayment(Request $request) {
		if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_DEBTOR')) {
			return new RedirectResponse('/admin');
		}
		$em = $this->getDoctrine()->getManager();
		$creditsRepo = $em->getRepository(Credit::class);
		$payment = new Payment();
		$payment->setDate(new \DateTime());
		$updatingCredit = $creditsRepo->find($request->query->get('creditId'));
		if ($updatingCredit == null) {
			return new RedirectResponse('/credits/');
		}
		/**
		 * @var Credit $updatingCredit
		 */
		$updatingCredit->increasePaymentsSum($request->query->get('sum'));
		$payment->setCredit($updatingCredit);
		$payment->setSum($request->query->get('sum'));
		$payment->setIgnored(false);
		if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
			$payment->setVerified(false);
		} else {
			$payment->setVerified(true);
		}
		$em->persist($payment);
		$em->persist($updatingCredit);
		$em->flush();
		return new RedirectResponse('/credits/'.$updatingCredit->getId());
	}
}