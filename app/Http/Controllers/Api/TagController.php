<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiWithAuthController;
use App\Http\Resources\Tag as TagResource;
use App\Repositories\TagRepository;

class TagController extends ApiWithAuthController
{
    public function __construct(TagRepository $tagRepository)
    {
        parent::__construct($tagRepository);
    }

    public function getResource()
    {
        return TagResource::class;
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
