<?php

namespace App\Controller;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ProjectController extends AbstractController
{
    #[Route('/project', name: 'add_project', methods: ['POST'])]
    public function addProject(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
		$body = $request->request->all();

        $project = new Project();
		try{
			$project->setName($body['name']);
			$project->setClient($body['client']);
		} catch(\Exception $e) {
			return new Response("Error: ".$e->getMessage(), 400);
		}

		$errors = $validator->validate($project);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }

		$entityManager->persist($project);
		$entityManager->flush();

		return new Response('Saved new project with id '.$project->getId().' it name is '.$project->getName());
    }

	#[Route('/project/{id}', name: 'get_project')]
    public function getProject(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $project = $entityManager->getRepository(Project::Class)->find($id);

        if (!$project) {
			return new JsonResponse(null,404);
        }

		$encoders = [new JsonEncoder()];
		$normalizers = [new ObjectNormalizer()];
		$serializer = new Serializer($normalizers, $encoders);

		$serialized = $serializer->serialize($project, 'json');
        return new JsonResponse([
			'result' => json_decode($serialized)
		]);
    }
}
