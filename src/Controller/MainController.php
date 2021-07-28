<?php

namespace App\Controller;

use App\Entity\Config;
use App\Repository\PricelistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\Credit;
use App\Entity\Pricelist;
use Doctrine\ORM\Query;

class MainController extends AbstractController
{
	/**
	 * @Route("/", name="index")
	 */
    public function indexPage()
    {
        $em = $this->getDoctrine()->getManager();
		/**
		 * @var PricelistRepository $repo
		 */
        $repo = $em->getRepository(Pricelist::Class);
        $plists = $repo->createQueryBuilder('c')->getQuery()->getResult(Query::HYDRATE_ARRAY);
		$min = min(array_column($plists, 'credit_from'));
		$max = max(array_column($plists, 'credit_to'));
        return $this->render('index.html.twig', ['pricelists' => $plists, 'max' => $max, 'min' => $min]);
    }

    /**
        * @Route("/takeCredit", name="takeCredit")
    */
    public function takeCredit(Request $request)
    {
    	if (!empty($this->getUser())) {
			$credit = new Credit();
			$em = $this->getDoctrine()->getManager();
			$credit->setAmount($request->query->get('credit'));
			$credit->setLength($request->query->get('length'));
			$credit->setDate(new \DateTime());
			$credit->setCustomerId($this->getUser());
			$this->calculateCredit($credit);
			$this->makeCreditDetalisation($credit);

			$em->persist($credit);
			$em->flush();
			return new RedirectResponse('/applications');
		} else {
			return new RedirectResponse('/register');
		}
    }

    private function calculateCredit(Credit $credit) {
		$em = $this->getDoctrine()->getManager();
		/**
		 * @var PricelistRepository $repo
		 */
		$repo = $em->getRepository(Pricelist::Class);
		$plists = $repo->createQueryBuilder('c')->getQuery()->getResult(Query::HYDRATE_ARRAY);
		$pricelist = $this->getPricelist($plists, $credit->getAmount(), $credit->getLength());

		$interest = $pricelist['interest'] / 100;
		$credit->setPrice($interest * ($credit->getLength() / 12) * $credit->getAmount());
	}

	private function getPricelist($plists, $sum, $length) {
    	$chosenPricelist = [];
    	foreach ($plists as $plist) {
			if ($plist['credit_from'] <= $sum && $plist['credit_to'] > $sum && $plist['length_from'] <= $length && $plist['length_to'] > $length) {
				$chosenPricelist = $plist;
			}
		}
    	return $chosenPricelist;
	}

	public static function makeCreditDetalisation(Credit $credit) {
    	$duration = $credit->getLength();
    	$monthlyAmount = round($credit->getAmount() / $duration, 2);
    	$monthlyPrice = round($credit->getPrice() / $duration, 2);
		$monthlyCost = $monthlyAmount + $monthlyPrice;
		$lastMonthAmount = round($credit->getAmount() - ($monthlyAmount * ($duration-1)),2);
		$lastMonthPrice = round($credit->getPrice() - ($monthlyPrice * ($duration-1)),2);
		$lastMonthCost = $lastMonthAmount + $lastMonthPrice;
		$detalisation = [];
		$acceptDate = !empty($credit->getAcceptDate()) ? $credit->getAcceptDate() : $credit->getDate();

		//var_dump(date_format(date_add($credit->getDate(), new \DateInterval('P1M')), "Y-m-d"));die();
    	for($i = 1; $i < $duration; $i++) {
    		$detalisation[] = ['date' => date_format(date_add($acceptDate, new \DateInterval('P1M')), "Y-m-d"), 'month' => $i, 'amount' => $monthlyAmount, 'price' => $monthlyPrice, 'payment' => $monthlyCost];
		}
    	$detalisation[] = ['date' => date_format(date_add($acceptDate, new \DateInterval('P1M')), "Y-m-d"), 'month' => $duration, 'amount' => $lastMonthAmount, 'price' => $lastMonthPrice, 'payment' => $lastMonthCost];
    	$credit->setDetalisation($detalisation);
	}

	/**
	 * @Route("/stdCreditInfo", name="stdCreditInfo")
	 */
	public function getStandartInfo(Request $request) {
		$em = $this->getDoctrine()->getManager();
		$configs = $em->getRepository(Config::Class)->findAll();
		$cfgs = [];
		foreach ($configs as $cfg) {
			$cfgs[$cfg->getName()] = $cfg->getValue();
		}
		if (!empty($cfgs['stdInfo'])) {
			$stdInfo = $cfgs['stdInfo'];
			unset($cfgs['stdInfo']);
			return $this->render('stdInfo.html.twig', $cfgs + ['stdInfo' => $stdInfo]);
		} else {
			return new RedirectResponse('/config');
		}
	}
}
