<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Rector;

use BitMx\DataEntities\DataEntity;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ResponseTypePropertyToAttributeRector\ResponseTypePropertyToAttributeRectorTest
 */
final class ResponseTypePropertyToAttributeRector extends AbstractRector
{
    // El namespace completo del atributo que vamos a agregar
    private const string SINGLE_ITEM_RESPONSE_ATTRIBUTE = 'BitMx\DataEntities\Attributes\SingleItemResponse';

    /**
     * Proporciona una descripción y ejemplos de lo que hace la regla.
     * Rector usa esto para generar documentación.
     */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Migrates the protected $responseType property to a #[SingleItemResponse] attribute for Single responses and removes it for COLLECTION responses.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
                    use BitMx\DataEntities\DataEntity;
                    use BitMx\DataEntities\Enums\ResponseType;
                    
                    class UserCollection extends DataEntity
                    {
                        protected ?ResponseType $responseType = ResponseType::COLLECTION;
                    }
                    
                    class UserResource extends DataEntity
                    {
                        protected ?ResponseType $responseType = ResponseType::Single;
                    }
                    CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
                    use BitMx\DataEntities\DataEntity;
                    use BitMx\DataEntities\Enums\ResponseType;
                    use BitMx\DataEntities\Attributes\SingleItemResponse;
                    
                    class UserCollection extends DataEntity
                    {
                    }
                    
                    #[SingleItemResponse]
                    class UserResource extends DataEntity
                    {
                    }
                    CODE_SAMPLE,
                ),
            ]);
    }

    /**
     * Especifica qué tipo de "Nodo" del código debe procesar esta regla.
     * En este caso, nos interesan las declaraciones de clases (Class_).
     *
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * Este es el método principal donde ocurre la magia.
     * Se ejecuta para cada nodo del tipo que especificamos arriba (cada Class_).
     *
     * @param  Class_  $node  El nodo de la clase actual que se está analizando.
     */
    public function refactor(Node $node): ?Node
    {
        // 1. Validar si la clase hereda de DataEntity.
        if ($node->extends === null) {
            return null;
        }
        if (! $this->isObjectType($node->extends, new ObjectType(DataEntity::class))) {
            return null;
        }

        $propertyToRemove = null;
        $isSingleResponse = false;
        $hasChanged = false; // Bandera para saber si hubo cambios

        // 2. Encontrar la propiedad y decidir la acción
        foreach ($node->getProperties() as $property) {
            if (! $this->isName($property, 'responseType')) {
                continue;
            }

            $defaultValue = $property->props[0]->default;
            if (! $defaultValue instanceof ClassConstFetch) {
                continue;
            }

            if ($this->isName($defaultValue->name, 'COLLECTION')) {
                $propertyToRemove = $property;
                break;
            }

            if ($this->isName($defaultValue->name, 'Single')) {
                $propertyToRemove = $property;
                $isSingleResponse = true;
                break;
            }
        }

        if ($propertyToRemove === null) {
            return null; // No hay nada que hacer
        }

        // 3. Eliminar el nodo de la propiedad del array de sentencias de la clase
        foreach ($node->stmts as $key => $stmt) {
            // Si la sentencia actual es la propiedad que queremos borrar...
            if ($stmt === $propertyToRemove) {
                // ...la eliminamos del array de sentencias.
                unset($node->stmts[$key]);
                $hasChanged = true;
                break;
            }
        }

        // 4. Agregar el atributo si es necesario
        if ($isSingleResponse) {
            $this->addSingleItemResponseAttribute($node);
            $hasChanged = true;
        }

        // 5. Devolver el nodo solo si ha cambiado
        if ($hasChanged) {
            return $node;
        }

        return null;
    }

    /**
     * Función auxiliar para crear y añadir el atributo a la clase.
     */
    private function addSingleItemResponseAttribute(Class_ $class): void
    {
        // Usar el nombre fully qualified directamente en el atributo
        $fullyQualifiedName = new FullyQualified(self::SINGLE_ITEM_RESPONSE_ATTRIBUTE);

        // Crear el nodo del atributo usando el nombre fully qualified
        $attribute = new Attribute($fullyQualifiedName);

        // Agrupa el atributo y lo añade al array de atributos de la clase.
        $class->attrGroups[] = new AttributeGroup([$attribute]);
    }
}
