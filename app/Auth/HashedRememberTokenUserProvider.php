<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;

class HashedRememberTokenUserProvider extends EloquentUserProvider implements UserProvider
{
    public function updateRememberToken(AuthenticatableContract $user, #[\SensitiveParameter] $token)
    {
        $user->setRememberToken($token);

        $timestamps = $user->timestamps;

        $user->timestamps = false;

        $user->forceFill([$user->getRememberTokenName() => Hash::make($token)])->save();

        $user->setRememberToken($token);

        $user->timestamps = $timestamps;
    }

    public function validateRememberToken(Authenticatable $user, #[\SensitiveParameter] $token): bool
    {
        $stored = $user->getRememberToken();

        if (! is_string($stored) || $stored === '') {
            return false;
        }

        if (Hash::isHashed($stored)) {
            return Hash::check($token, $stored);
        }

        return hash_equals($stored, $token);
    }

    public function retrieveByToken($identifier, #[\SensitiveParameter] $token)
    {
        $model = $this->createModel();

        $retrievedModel = $this->newModelQuery($model)->where(
            $model->getAuthIdentifierName(), $identifier
        )->first();

        if (! $retrievedModel) {
            return null;
        }

        return $this->validateRememberToken($retrievedModel, $token) ? $retrievedModel : null;
    }
}