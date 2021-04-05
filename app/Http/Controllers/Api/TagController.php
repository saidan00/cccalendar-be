<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiWithAuthController;
use App\Repositories\TagRepository;

class TagController extends ApiWithAuthController
{
    public function __construct(TagRepository $tagRepository)
    {
        $this->repository = $tagRepository;
    }

    protected function getValidationRules()
    {
        return [];
    }

    protected function getValidationMessages()
    {
        return [];
    }
}
