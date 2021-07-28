<?php

namespace App\Controller;

use App\Entity\Credit;
use App\Entity\Writeoff;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class WriteoffController extends AbstractController
{
	/**
	 * @Route("/writeoffs", name="writeoffs")
	 */
	public function WriteoffsIndex() {
		$em = $this->getDoctrine()->getManager()->getRepository(Writeoff::class);
		if($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPER_ADMIN')) {
			$writeoffs = $em->findAll();
			return $this->render('writeoffs.html.twig', ['writeoffs' => $writeoffs]);
		}
		return new RedirectResponse('/admin');
	}

	/**
	 * @Route("/addWriteoff", name="addWriteoff", methods={"GET", "HEAD"})
	 */
	public function AddWriteOff(Request $request) {
		if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
			return new RedirectResponse('/admin');
		}
		$em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository(Credit::class);
		$writeoff = new Writeoff();
		$writeoff->setDate(new \DateTime());
		/**
		 * @var Credit $updatingCredit
		 */
		$updatingCredit = $repo->findBy(['id' => $request->query->get('creditId')])[0];
		if (empty($updatingCredit)) {
			return new RedirectResponse('/credits/');
		}
		if (empty($request->query->get('sum'))) {
			return new RedirectResponse('/credits/'.$request->query->get('creditId'));
		}
		$writeoff->setAmount($request->query->get('sum'));
		$writeoff->setReason($request->query->get('reason'));
		$updatingCredit->addWriteoff($writeoff);

		$em->persist($writeoff);
		$em->flush();
		$em->persist($updatingCredit);
		$em->flush();
		return new RedirectResponse('/credits/'.$request->query->get('creditId'));
	}
}