<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\ProductInput;
use App\Service\ProductService;
use App\Service\TestProductFactory;
use App\Validation\ProductValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ProductController
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly ProductValidator $validator,
        private readonly TestProductFactory $testProductFactory
    ) {
    }

    public function create(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $errors = $this->validator->validate($data);
        if (!empty($errors)) {
            return new JsonResponse(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $input = ProductInput::fromArray($data);
            $product = $this->productService->createProduct($input);
            return new JsonResponse($product, Response::HTTP_CREATED);
        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), 'duplicate key value violates unique constraint')) {
                return new JsonResponse(['error' => 'Duplicate product name'], Response::HTTP_CONFLICT);
            }
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, int $id): Response
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $errors = $this->validator->validate($data, $id);
        if (!empty($errors)) {
            return new JsonResponse(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $input = ProductInput::fromArray($data);
            $product = $this->productService->updateProduct($id, $input);
            if (!$product) {
                return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
            }
            return new JsonResponse($product, Response::HTTP_OK);
        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), 'duplicate key value violates unique constraint')) {
                return new JsonResponse(['error' => 'Duplicate product name'], Response::HTTP_CONFLICT);
            }
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(int $id): Response
    {
        if ($this->productService->deleteProduct($id)) {
            return new Response(null, Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
    }

    public function list(Request $request): Response
    {
        $page = (int)$request->query->get('page', 1);
        $limit = (int)$request->query->get('limit', 20);
        $category = $request->query->get('category');
        $brand = $request->query->get('brand');

        if ($limit > 100) $limit = 100;
        if ($limit < 1) $limit = 20;
        if ($page < 1) $page = 1;

        $result = $this->productService->listProducts($page, $limit, $category, $brand);
        return new JsonResponse($result);
    }

    public function createTest(Request $request): Response
    {
        $source = $request->query->get('source');

        try {
            if ($source === 'local') {
                $product = $this->testProductFactory->createFromLocal();
            } elseif ($source === 'remote') {
                $product = $this->testProductFactory->createFromRemote();
            } else {
                return new JsonResponse(['error' => 'Invalid or missing source parameter'], Response::HTTP_BAD_REQUEST);
            }

            return new JsonResponse($product, Response::HTTP_CREATED);
        } catch (Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function health(): Response
    {
        return new JsonResponse(['status' => 'ok']);
    }
}
