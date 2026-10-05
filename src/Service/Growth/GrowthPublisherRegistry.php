<?php

namespace App\Service\Growth;

use App\Enum\GrowthDestination;

class GrowthPublisherRegistry
{
    /** @param iterable<GrowthPublisherInterface> $publishers */
    public function __construct(private readonly iterable $publishers) {}

    public function forDestination(GrowthDestination $destination): GrowthPublisherInterface
    {
        foreach ($this->publishers as $publisher) {
            if ($publisher->supports($destination)) {
                return $publisher;
            }
        }

        throw new \RuntimeException(sprintf('No Growth publisher for %s.', $destination->value));
    }
}
