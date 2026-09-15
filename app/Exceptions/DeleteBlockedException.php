<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class DeleteBlockedException extends RuntimeException
{
    public static function forModel(Model $model): self
    {
        $label = method_exists($model, 'getDeleteLabel')
            ? $model->getDeleteLabel()
            : class_basename($model);

        return new self("This {$label} cannot be deleted because it is still in use.");
    }
}
