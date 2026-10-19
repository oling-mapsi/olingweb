<?php

namespace App\Controller;

use App\Entity\Team;
use App\Dto\StructuredContentAdminFormData;
use App\Form\TeamType;
use App\Repository\TeamRepository;
use App\Service\StructuredContentTranslationSynchronizer;
use App\Service\UploadManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/teams')]
class TeamAdminController extends AbstractController
{
    #[Route('', name: 'admin_teams_index', methods: ['GET'])]
    #[Route('/', name: 'admin_teams_index_slash', methods: ['GET'])]
    public function index(TeamRepository $repository): Response
    {
        return $this->redirectToRoute('admin_site_section',['section'=>'societe','_fragment'=>'equipe']);
    }

    #[Route('/new', name: 'admin_teams_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UploadManager $uploadManager, StructuredContentTranslationSynchronizer $translationSynchronizer): Response
    {
        $team = new Team();
        $formData = new StructuredContentAdminFormData();
        $form = $this->createForm(TeamType::class, $formData, ['data_class' => StructuredContentAdminFormData::class]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photoFile')->getData();

            if ($photoFile) {
                try {
                    $formData->setPhoto($uploadManager->upload($photoFile, 'teams/photos'));
                } catch (FileException $exception) {
                    $this->addFlash('danger', "Impossible d'envoyer la photo.");
                }
            }

            $team->setNoncomplet((string) $formData->getNoncomplet());
            $entityManager->persist($team);
            $entityManager->flush();
            $translationSynchronizer->saveTeamFromForm($team, $formData);
            $entityManager->flush();

            $this->addFlash('success', 'Membre créé.');

            return $this->redirectToRoute('admin_teams_index');
        }

        return $this->render('admin/teams/new.html.twig', [
            'form' => $form,
            'team' => $team,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_teams_edit', methods: ['GET', 'POST'])]
    public function edit(Team $team, Request $request, EntityManagerInterface $entityManager, UploadManager $uploadManager, StructuredContentTranslationSynchronizer $translationSynchronizer): Response
    {
        $originalPhoto = $team->getPhoto();

        $formData = $translationSynchronizer->teamFormData($team);
        $form = $this->createForm(TeamType::class, $formData, ['data_class' => StructuredContentAdminFormData::class]);
        $profileRaw=$entityManager->getConnection()->fetchOne('SELECT public_profile FROM team_translation WHERE team_id=? AND locale=?',[$team->getId(),'fr']);
        $profile=$profileRaw?json_decode($profileRaw,true,512,JSON_THROW_ON_ERROR):[];$profile=is_array($profile)?$profile:[];
        $visibleName=$profile['displayName']??$team->getNoncomplet();$visiblePhoto=$profile['photo']??$team->getPhoto();
        $form->add('publicDisplayName',\Symfony\Component\Form\Extension\Core\Type\TextType::class,['mapped'=>false,'required'=>false,'label'=>'Nom affiché sur le site','data'=>$visibleName]);
        $form->add('publicPhoto',\Symfony\Component\Form\Extension\Core\Type\TextType::class,['mapped'=>false,'required'=>false,'label'=>'Photo affichée sur le site (chemin ou URL)','data'=>$visiblePhoto]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photoFile')->getData();

            if ($photoFile) {
                try {
                    $formData->setPhoto($uploadManager->upload($photoFile, 'teams/photos'));

                } catch (FileException $exception) {
                    $this->addFlash('danger', "Impossible d'envoyer la photo.");
                }
            }

            $translationSynchronizer->saveTeamFromForm($team, $formData);
            $updatedProfile=$profile;
            $newName=trim((string)$form->get('publicDisplayName')->getData());$newPhoto=trim((string)$form->get('publicPhoto')->getData());
            if($newName!==(string)$visibleName){if($newName==='')unset($updatedProfile['displayName']);else $updatedProfile['displayName']=$newName;}
            if($newPhoto!==(string)$visiblePhoto){if($newPhoto==='')unset($updatedProfile['photo']);else $updatedProfile['photo']=$newPhoto;}
            if($photoFile && $newPhoto===(string)$visiblePhoto)$updatedProfile['photo']=$formData->getPhoto();
            if($updatedProfile!==$profile)$translationSynchronizer->saveTeamPublicProfile($team,$updatedProfile);

            $entityManager->flush();

            $this->addFlash('success', 'Membre mis à jour.');

            return $this->redirectToRoute('admin_teams_index');
        }

        return $this->render('admin/teams/edit.html.twig', [
            'form' => $form,
            'team' => $team,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_teams_delete', methods: ['POST'])]
    public function delete(Request $request, Team $team, EntityManagerInterface $entityManager, UploadManager $uploadManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$team->getId(), $request->request->get('_token'))) {

            $entityManager->remove($team);
            $entityManager->flush();
            $this->addFlash('success', 'Membre supprimé.');
        }

        return $this->redirectToRoute('admin_teams_index');
    }
}
