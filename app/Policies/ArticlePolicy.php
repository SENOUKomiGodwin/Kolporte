<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'marchand';
    }

    public function view(User $user, Article $article): bool
    {
        return $this->possede($user, $article);
    }

    public function create(User $user): bool
    {
        return $user->role === 'marchand';
    }

    public function update(User $user, Article $article): bool
    {
        return $this->possede($user, $article);
    }

    public function delete(User $user, Article $article): bool
    {
        return $this->possede($user, $article);
    }

    private function possede(User $user, Article $article): bool
    {
        return $user->boutiques()->whereKey($article->boutique_id)->exists();
    }
}
