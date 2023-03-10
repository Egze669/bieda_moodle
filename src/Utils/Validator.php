<?php
namespace App\Utils;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use function Symfony\Component\String\u;
class Validator
{

    public function validatePassword(?string $plainPassword): string
    {
        if (empty($plainPassword)) {
            throw new InvalidArgumentException('The password can not be empty.');
        }

        if (u($plainPassword)->trim()->length() < 6) {
            throw new InvalidArgumentException('The password must be at least 6 characters long.');
        }

        return $plainPassword;
    }
    public function validateRoles(?string $role): string
    {
        if (empty($role)) {
            throw new InvalidArgumentException('The role can not be empty.');
        }

        if (u($role)->trim()->upper()  != 'ROLE_STUDENT' && u($role)->trim()->upper() != 'ROLE_TEACHER') {
            throw new InvalidArgumentException('The role can only be ROLE_TEACHER or ROLE_STUDENT');
        }

        return $role;
    }
    public function validateEmail(?string $email): string
    {
        if (empty($email)) {
            throw new InvalidArgumentException('The email can not be empty.');
        }

        if (null === u($email)->indexOf('@')) {
            throw new InvalidArgumentException('The email should look like a real email.');
        }

        return $email;
    }

    public function validateName(?string $name): string
    {
        if (empty($name)) {
            throw new InvalidArgumentException('Name can not be empty.');
        }
        return $name;
    }
    public function validateSurname(?string $surname): string
    {
        if (empty($surname)) {
            throw new InvalidArgumentException('Surname can not be empty.');
        }
        return $surname;
    }
}
