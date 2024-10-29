<?php

namespace App\Controller;

use App\Entity\Developer;
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

class DeveloperController extends AbstractController
{
    #[Route('/developer', name: 'add_developer', methods: ['POST'])]
    public function addDeveloper(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
		$body = $request->request->all();

        $developer = new Developer();
		try
		{
			$developer->setName($body['name']);
			$developer->setJob($body['job']);
			$developer->setEmail($body['email'] ?? null);
			$developer->setPhone($body['phone'] ?? null);
		} catch(\Exception $e) {
			return new Response("Error: ".$e->getMessage(), 400);
		}

		$errors = $validator->validate($developer);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }

		$entityManager->persist($developer);
		$entityManager->flush();

		return new Response('Saved new developer with id '.$developer->getId().' his name is '.$developer->getName());
    }

	#[Route('/developer/edit/{id}', name: 'edit_developer', methods: ['POST'])]
    public function editDeveloper(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, int $id): Response
    {
		$developer = $entityManager->getRepository(Developer::Class)->find($id);
        if (!$developer) {
			return new JsonResponse(null,404);
        }

		$body = $request->request->all();

		try
		{
			$developer->setName($body['name'] ?? $developer->getName());
			$developer->setJob($body['job'] ?? $developer->getJob());
			$developer->setEmail($body['email'] ?? $developer->getEmail());
			$developer->setPhone($body['phone'] ?? $developer->getPhone());
		} catch(\Exception $e) {
			return new Response("Error: ".$e->getMessage(), 400);
		}

		$errors = $validator->validate($developer);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }

		$entityManager->persist($developer);
		$entityManager->flush();

		return new Response('Edited developer with id '.$developer->getId().' his name is '.$developer->getName());
    }

	#[Route('/developer/{id}', name: 'get_developer', methods: ['GET'])]
    public function getDeveloper(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $developer = $entityManager->getRepository(Developer::Class)->find($id);
        if (!$developer) {
			return new JsonResponse(null,404);
        }

		$encoders = [new JsonEncoder()];
		$normalizers = [new ObjectNormalizer()];
		$serializer = new Serializer($normalizers, $encoders);

		$serialized = $serializer->serialize($developer, 'json', [
			'circular_reference_handler' => function (object $object) {
				return $object->getId();
			}
		]);

		$response = new JsonResponse([
			'result' => json_decode($serialized)
		]);
		$response->setEncodingOptions(JSON_UNESCAPED_UNICODE);
        return $response;
    }

	#[Route('/developer/{id}', name: 'delete_developer', methods: ['DELETE'])]
	public function deleteDeveloper(EntityManagerInterface $entityManager, int $id): Response
    {
		$developer = $entityManager->getRepository(Developer::Class)->find($id);
        if (!$developer) {
			return new Response('Developer with id '.$id.' not found',404);
        }

		$entityManager->remove($developer);
		$entityManager->flush();

		return new Response('Removed developer with id '.$id);
	}
}
