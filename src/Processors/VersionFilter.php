<?php declare(strict_types=1);

namespace L5Swagger\Processors;

use OpenApi\Analysis;
use OpenApi\Generator;
use OpenApi\Processors\Concerns\AnnotationTrait;

/**
 * Allows to filter endpoints based on versions.
 */
class VersionFilter
{
    use AnnotationTrait;

    protected array $versions = [];

    public function __construct(array $versions = [])
    {
        $this->versions = $versions;
    }

    public function __invoke(Analysis $analysis): void
    {
        if ($this->versions && !Generator::isDefault($analysis->openapi->paths)) {
            $filtered = [];
            foreach ($analysis->openapi->paths as $pathItem) {
                $matched = null;
                foreach ($pathItem->operations() as $operation) {
                    if (!Generator::isDefault($operation->x)
                        && isset($operation->x['versions'])
                        && is_array($operation->x['versions'])
                    ) {
                        if (!array_diff($this->versions, $operation->x['versions'])) {
                            $matched = $pathItem;
                            break;
                        }
                    }
                }

                if ($matched) {
                    $filtered[] = $matched;
                } else {
                    $this->removeAnnotation($analysis->annotations, $pathItem);
                }
            }

            $analysis->openapi->paths = $filtered;
        }
    }
}
