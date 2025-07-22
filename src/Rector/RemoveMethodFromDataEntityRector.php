<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Rector;

use BitMx\DataEntities\DataEntity;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class RemoveMethodFromDataEntityRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove the protected $method property from classes that extend BitMx\DataEntities\DataEntity',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
                    use BitMx\DataEntities\DataEntity;
                    use Method;
                    
                    class UserEntity extends DataEntity
                    {
                        protected ?Method $method = Method::SELECT;
                        protected string $name;
                    }
                    CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
                    use BitMx\DataEntities\DataEntity;
                    use Method;
                    
                    class UserEntity extends DataEntity
                    {
                        protected string $name;
                    }
                    CODE_SAMPLE,
                ),
            ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param  Class_  $node
     */
    public function refactor(Node $node): ?Node
    {
        // Verificar si la clase extiende de DataEntity
        if (! $this->isObjectType($node, new ObjectType(DataEntity::class))) {
            return null;
        }

        $hasChanged = false;

        // Buscar y eliminar la propiedad $method
        foreach ($node->stmts as $key => $stmt) {
            if (! $stmt instanceof Property) {
                continue;
            }

            // Verificar si es una propiedad llamada "method"
            foreach ($stmt->props as $propertyKey => $property) {
                if (! $this->isName($property->name, 'method')) {
                    continue;
                }

                // Si la propiedad tiene múltiples declaraciones, solo eliminar la de "method"
                if (count($stmt->props) > 1) {
                    unset($stmt->props[$propertyKey]);
                    $stmt->props = array_values($stmt->props);
                } else {
                    // Si es la única propiedad en esta declaración, eliminar toda la declaración
                    unset($node->stmts[$key]);
                }

                $hasChanged = true;
                break;
            }
        }

        if ($hasChanged) {
            // Reindexar el array de statements
            $node->stmts = array_values($node->stmts);

            return $node;
        }

        return null;
    }
}
