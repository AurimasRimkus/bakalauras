<?php

namespace App\Controller;

use App\Entity\Config;
use App\Entity\Credit;
use App\Entity\User;
use App\Repository\CreditRepository;
use App\Service\ConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class CreditsController extends AbstractController
{
	/**
	 * @Route("/admin", name="admin")
	 * @return Response
	 */
	public function AdminIndex(ConfigService $cfg) {
		$em = $this->getDoctrine()->getManager()->getRepository(Credit::class);
		if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPER_ADMIN')) {
			$credits = $em->findAll();
		} else if ($this->getUser()) {
			/**
			 * @var User $user
			 */
			$user = $this->getUser();
			$credits = $em->findBy(['customer_id' => $user->getId()]);
		} else {
			return new RedirectResponse('/');
		}
		return $this->render('adminIndex.html.twig', ['credits' => $credits, 'mode' => 'credits', 'config' => $cfg]);
	}

	/**
	 * @Route("/applications", name="applications")
	 * @param ConfigService $cfg
	 * @return RedirectResponse|Response
	 */
	public function ApplicationsIndex(ConfigService $cfg) {
		/**
		 * @var CreditRepository $em
		 */
		$em = $this->getDoctrine()->getManager()->getRepository(Credit::class);
		if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPER_ADMIN')) {
			$credits = $em->findAllApplications();
		} else if ($this->getUser()) {
			/**
			 * @var User $user
			 */
			$user = $this->getUser();
			$credits = $em->findAllApplicationsBy('u.customer_id = '. $user->getId());
		} else {
			return new RedirectResponse('/');
		}
		return $this->render('adminIndex.html.twig', ['credits' => $credits, 'mode' => 'applications', 'config' => $cfg]);
	}

	/**
	 * @Route("/acceptApplication/{id}", name="acceptApplication", methods={"GET", "HEAD"})
	 * @return RedirectResponse
	 */
	public function AcceptApplication(Request $request, int $id) {
		if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
			return new RedirectResponse('/admin');
		}
		$em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository(Credit::class);
		$repoUsers = $em->getRepository(User::class);
		/**
		 * @var Credit $credit
		 */
		$credit = $repo->find($id);
		/**
		 * @var User $user
		 */
		$user = $repoUsers->find($credit->getCustomerId());
		if (empty($credit->getAcceptDate())) {
			$user->addRole('ROLE_DEBTOR');
			$credit->setAcceptDate(new \DateTime());
			$em->persist($credit);
			$em->flush();
		}
		return new RedirectResponse('/applications');
	}

	/**
	 * @Route("/rejectApplication/{id}", name="rejectApplication", methods={"GET", "HEAD"})
	 * @return RedirectResponse
	 */
	public function RejectApplication(Request $request, int $id) {
		if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
			return new RedirectResponse('/admin');
		}
		$em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository(Credit::class);
		/**
		 * @var Credit $credit
		 */
		$credit = $repo->find($id);
		if (empty($credit->getAcceptDate())) {
			$em->remove($credit);
			$em->flush();
		}
		return new RedirectResponse('/applications');
	}

	/**
	 * @Route("/credits/{id}", name="credits", methods={"GET", "HEAD"})
	 * @return Response
	 */
	public function ViewCredit(Request $request, int $id, ConfigService $cfg) {
		/**
		 * @var array<int> $creditIds
		 */
		$creditIds = $this->getCreditIdsOfUser();
		if (empty($this->getUser()) || (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN') && !in_array($id, $creditIds))) {
			return new RedirectResponse('/login');
		}
		$em = $this->getDoctrine()->getManager()->getRepository(Credit::class);
		/**
		 * @var Credit $credit
		 */
		$credit = $em->find($id);
		if (empty($credit)) {
			return new RedirectResponse('/admin');
		}
		$this->recalculateCredit($credit);
		return $this->render('credit.html.twig', ['credit' => $credit, 'config' => $cfg]);
	}

	/**
	 * @Route("/recalcBalance/{id}", name="recalcBalance", methods={"GET", "HEAD"})
	 * @return RedirectResponse
	 */
	public function recalculateBalance(Request $request, int $id) {
		if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
			return new RedirectResponse('/admin');
		}
		$creditRepository = $this->getDoctrine()->getManager()->getRepository(Credit::class);
		/**
		 * @var Credit $credit
		 */
		$credit = $creditRepository->find($id);
		$this->recalculateCredit($credit);
		return new RedirectResponse('/credits/'.$credit->getId());
	}

	/**
	 * @param Credit $credit
	 * @return void
	 */
	public function recalculatePaymentsSum(Credit $credit){
		$em = $this->getDoctrine()->getManager();
		$totalPaid = 0;
		foreach ($credit->getActivePayments() as $payment) {
			$totalPaid += $payment->getSum();
		}
		$credit->setPaymentsSum($totalPaid);
		$em->persist($credit);
		$em->flush();
	}

	/**
	 * @param Credit $credit
	 * @return void
	 */
	protected function recalculateCredit(Credit $credit) {
		$this->recalculatePaymentsSum($credit);
		if ($credit->hasDetalisation()) {
			$this->recalculateDetalisation($credit);
			//$this->recalculateSceduleDelays($credit);
		}
	}

	/**
	 * @param Credit $credit
	 * @throws \Exception
	 * @return void
	 */
	protected function recalculateDetalisation(Credit $credit) {
		$em = $this->getDoctrine()->getManager();
		MainController::makeCreditDetalisation($credit);
		$detalisation = $credit->getDetalisation();
		$paymentsSum = $credit->getPaymentsSum() + $credit->getWriteoffsSum();
		foreach ($detalisation as &$item) {
			if (date_sub(new \DateTime($item->date), new \DateInterval('P1M')) < new \DateTime()) { // liko menuo iki imokos, jau uzmetam debt
				if($paymentsSum > 0) {
					if ($item->payment <= $paymentsSum) {
						$paymentsSum -= $item->payment;
						$item->debt = 0;
					} else {
						$item->debt = $item->payment - $paymentsSum;
						$paymentsSum = 0;
					}
				} else {
					$item->debt = $item->payment;
				}
			}
			if (new \DateTime($item->date) < new \DateTime() && $item->debt > 0) { // imokos data senesne nei dabartine diena
				$item->delay = date_diff(new \DateTime($item->date), new \DateTime(), true)->days;
			} else if (empty($item->delay)) {
				$item->delay = 0; // jei tuscias, nustatom 0. jei yra delay, tai paliekam.
			}
		}
		$credit->setDetalisation($detalisation);
		$em->persist($credit);
		$em->flush();
	}

	/**
	 * @return array<int,int>|RedirectResponse
	 *
	 */
	protected function getCreditIdsOfUser() {
		if (empty($this->getUser())) {
			return new RedirectResponse('/login');
		}
		$creditIds = [];
		/**
		 * @var User $user
		 */
		$user = $this->getUser();
		foreach ($user->getCredits() as $credit) {
			$creditIds[] = $credit->getId();
		}
		return $creditIds;
	}
}