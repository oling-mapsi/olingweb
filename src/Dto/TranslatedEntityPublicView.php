<?php

namespace App\Dto;

final class TranslatedEntityPublicView
{
    /**
     * @param array<string, mixed> $translatedFields
     * @param array<string, callable> $relationResolvers
     */
    public function __construct(
        private readonly object $entity,
        private readonly array $translatedFields,
        private readonly array $relationResolvers = [],
    ) {
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function __call(string $name, array $arguments): mixed
    {
        if (isset($this->relationResolvers[$name])) {
            return ($this->relationResolvers[$name])(...$arguments);
        }

        if (str_starts_with($name, 'get')) {
            $field = lcfirst(substr($name, 3));
            if (array_key_exists($field, $this->translatedFields)) {
                return $this->translatedFields[$field];
            }
        }

        if (str_starts_with($name, 'is')) {
            $field = lcfirst(substr($name, 2));
            if (array_key_exists($field, $this->translatedFields)) {
                return (bool) $this->translatedFields[$field];
            }
        }

        if (method_exists($this->entity, $name)) {
            return $this->entity->{$name}(...$arguments);
        }

        throw new \BadMethodCallException(sprintf('Method "%s" is not available on translated view for "%s".', $name, $this->entity::class));
    }
}
