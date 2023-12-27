<?php

namespace App\Contracts;

interface AuthServiceInterface
{
    public function register(array $data);
    public function login(array $data);
    public function logout();
    public function setUserPreferences(array $data);
}
