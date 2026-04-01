<?php

namespace App\Controller;

use App\Entity\MatchNba;
use App\Entity\Pari;
use App\Form\MatchFormType;
use App\Repository\MatchNbaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/matchs')]
#[IsGranted('ROLE_ADMIN')]
class AdminMatchController extends AbstractController
{
    #[Route('', name: 'admin_matchs_index')]
    public function index(MatchNbaRepository $matchNbaRepository): Response
    {
        $matchs = $matchNbaRepository->findBy([], ['dateMatch' => 'DESC']);

        return $this->render('admin/matchs/index.html.twig', [
            'matchs' => $matchs,
        ]);
    }

    #[Route('/new', name: 'admin_matchs_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $match = new MatchNba();
        $form = $this->createForm(MatchFormType::class, $match);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($match);
            $em->flush();

            $this->addFlash('success', 'Match créé avec succès');
            return $this->redirectToRoute('admin_matchs_index');
        }

        return $this->render('admin/matchs/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/resultat', name: 'admin_matchs_resultat', methods: ['GET', 'POST'])]
    public function resultat(MatchNba $match, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $scoreTeam1 = (int) $request->request->get('scoreTeam1', 0);
            $scoreTeam2 = (int) $request->request->get('scoreTeam2', 0);

            $match->setScoreTeam1($scoreTeam1);
            $match->setScoreTeam2($scoreTeam2);
            $match->setStatut('termine');

            if ($scoreTeam1 > $scoreTeam2) {
                $match->setResultat($match->getTeam1());
            } else {
                $match->setResultat($match->getTeam2());
            }

            $em->flush();

            $paris = $em->getRepository(Pari::class)->findBy(['statut' => 'en_cours']);
            foreach ($paris as $pari) {
                $equipes = explode(' + ', $pari->getEquipe());
                $concerne = in_array($match->getTeam1(), $equipes) || in_array($match->getTeam2(), $equipes);

                if (!$concerne) continue;

                if (count($equipes) === 1) {
                    if ($pari->getEquipe() === $match->getResultat()) {
                        $pari->setStatut('gagne');
                        $pari->setGains($pari->getMise() * $pari->getCote());
                        $pari->getUser()->setSolde($pari->getUser()->getSolde() + $pari->getGains());
                    } else {
                        $pari->setStatut('perdu');
                        $pari->setGains(0);
                    }
                }
            }

            $em->flush();

            $this->addFlash('success', 'Résultat enregistré et paris mis à jour');
            return $this->redirectToRoute('admin_matchs_index');
        }

        return $this->render('admin/matchs/resultat.html.twig', [
            'match' => $match,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_matchs_delete', methods: ['POST'])]
    public function delete(MatchNba $match, EntityManagerInterface $em): Response
    {
        $em->remove($match);
        $em->flush();

        $this->addFlash('success', 'Match supprimé');
        return $this->redirectToRoute('admin_matchs_index');
    }
}
