<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Contracts\AuthServiceInterface;

class AuthService implements AuthServiceInterface
{
    public function register(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
        $token = $user->createToken("authToken")->accessToken;
        $user['token'] = $token;

        return $user;
    }

    public function login(array $data)
    {
        if (auth()->attempt($data)) {
            $user = auth()->user();
            $user['token'] =  $user->createToken("authToken")->accessToken;
            return $user;
        }
        throw new \Exception("Invalid credentials", 401);
    }

    public function logout()
    {
        return auth()->user()->token()->revoke();
    }

    public function setUserPreferences($data)
    {
        $user = auth()->user();
        $this->setCategoryPreferences($data, $user);
        $this->setSourcePreferences($data, $user);
        $this->setAuthorPreferences($data, $user);

        return $user->load('categories', 'sources', 'authors');
    }

    public function setCategoryPreferences($data, $user)
    {
        $formatCategory = collect($data['selectedCategories'])->map(function ($category) {
            return [
                'category_id' => $category['id'],
            ];
        });
        $user->categories()->sync($formatCategory);

        return $user;
    }

    public function setSourcePreferences($data, $user)
    {
        $formatSource = collect($data['selectedSources'])->map(function ($source) {
            return [
                'source_id' => $source['id'],
            ];
        });
        $user->sources()->sync($formatSource);

        return $user;
    }

    public function setAuthorPreferences($data, $user)
    {
        $formatAuthor = collect($data['selectedAuthors'])->map(function ($author) {
            return [
                'author_id' => $author['id'],
            ];
        });
        $user->authors()->sync($formatAuthor);

        return $user;
    }
}
