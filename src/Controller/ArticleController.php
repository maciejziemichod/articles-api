<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    public const LIMIT = 10;

    #[Route('/api/articles', name: 'articles', methods: ['GET'])]
    public function getArticles(Request $request, ArticleRepository $articleRepository): Response
    {
        $totalArticlesNumber = $articleRepository->getNumberOfArticles();
        // changing limit would be really easy, just making LIMIT not const, then checking the query if the limit is provided
        $pagesNumber = ceil($totalArticlesNumber / static::LIMIT);

        $pageNumber = 1;
        $requestedPage = $request->query->getInt("page");
        if ($requestedPage) {
            $pageNumber = $requestedPage;
        }

        $offset = ($pageNumber - 1) * static::LIMIT;

        // if user wants page that does not exist, for example there are 5 of them and he does "?page=7", he will get
        // the response but with empty data
        $articles = $articleRepository->findBy([], ["createdAt" => "DESC"], static::LIMIT, $offset);

        $response = [
            "code" => 200,
            "meta" => [
                "pagination" => [
                    "total" => $totalArticlesNumber,
                    "pages" => $pagesNumber,
                    "page" => $pageNumber,
                    "limit" => static::LIMIT
                ]
            ],
            "data" => $articles
        ];
        return $this->json($response);
    }

    #[Route('/api/articles/new', name: 'article_new', methods: ['POST'])]
    public function newArticle(Request $request): Response
    {
        // first it checks if Authorization: Bearer [token] header is provided
        $authErrorResponse = ["code" => 401, "data" => []];
        if (!$request->headers->has("Authorization")) {
            $authErrorResponse["data"]["message"] = "No authorization header";
            return $this->json($authErrorResponse, 401);
        }
        if (0 !== strpos($request->headers->get("Authorization"), 'Bearer ')) {
            $authErrorResponse["data"]["message"] = "Authorization does not contain Bearer";
            return $this->json($authErrorResponse, 401);
        }
        // then it checks if the token is valid, here the token is hard coded but it normally would be a separate entity
        // and probably I would use Guard if there were actual users, which I send the tokens to 
        $token = substr($request->headers->get("Authorization"), 7);
        if ($token !== "authtoken1337") {
            $authErrorResponse["data"]["message"] = "Invalid API Token";
            return $this->json($authErrorResponse, 401);
        }

        $response = ["code" => 422];
        $article = new Article();
        $data = $request->toArray();

        // checks if all the required info is provided
        if (!isset($data["name"]) || strlen($data["name"]) === 0) {
            $response["data"] = [
                ["name" => "wasn't provided"]
            ];
            return $this->json($response, 422);
        }
        if (!isset($data["text"]) || strlen($data["text"]) === 0) {
            $response["data"] = [
                ["text" => "wasn't provided"]
            ];
            return $this->json($response, 422);
        }
        if (strlen($data["text"]) < 150) {
            $response["data"] = [
                ["text" => "was too short (min 150 characters)"]
            ];
            return $this->json($response, 422);
        }

        // valid, set article's properties
        $article->setName($data["name"]);
        $article->setText($data["text"]);

        // enter to database
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($article);
        $entityManager->flush();

        $response["code"] = 201;
        $response["data"] = $article;

        return $this->json($response, 201);
    }
}
