<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api', name: 'api_')]
class DockerController extends AbstractController
{
    #[Route('/docker', name: 'app_docker', methods: ['POST'])]
    public function index(Request $request): Response
    {
        $post = ($request->getContent());
        // write the result in a file
        $file = fopen("/tmp/here.txt", "w");
        fwrite($file,$post);
        fclose($file);

        return new JsonResponse($post);
    }
    //create a new route with get method to test if my server is working, it's suppoosed to return a json response
    #[Route('/cc', name: 'app_docker_get', methods: ['GET'])]
    public function indexGet(Request $request): Response
    {
        $data = ['message' => 'Hello World'];
        return new JsonResponse($data);
    }
}
