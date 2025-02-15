<?php

namespace App\Http\Controllers\API;

use App\Contracts\NewsRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\NewsFilterRequest;
use App\Http\Resources\NewsResource;
use Illuminate\Http\JsonResponse;

class NewsController extends Controller
{
    public function __construct(private NewsRepository $repository) {}

    public function index(NewsFilterRequest $request): JsonResponse
    {
        $news = $this->repository->getFiltered($request->validated());

        return NewsResource::collection($news)->response();
    }
}
