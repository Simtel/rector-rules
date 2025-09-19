<?php

declare(strict_types=1);

namespace Simtel\RectorRules\Rector;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\UnionType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\Exception\PoorDocumentationException;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Renames 'find*' methods to 'get*' when they return a non-nullable entity type.
 *
 * This rule helps enforce naming conventions where:
 * - 'find*' methods return nullable types (might not find the entity)
 * - 'get*' methods return non-nullable types (always return the entity or throw)
 */
final class RenameFindAndGetMethodCallRector extends AbstractRector
{
    private const FIND_PREFIX = 'find';
    private const GET_PREFIX = 'get';

    /**
     * Primitive types that should not be considered for renaming
     */
    private const PRIMITIVE_TYPES = [
        'int',
        'string',
        'bool',
        'float',
        'array',
        'object',
        'mixed',
        'void',
    ];
    /**
     * @throws PoorDocumentationException
     */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Rename find* methods to get* when they return only entity (not nullable)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class UserRepository
{
    public function findUserById(int $id): User
    {
        // ...
    }
    
    public function findUserByEmail(string $email): ?User
    {
        // ...
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class UserRepository
{
    public function getUserById(int $id): User
    {
        // ...
    }
    
    public function findUserByEmail(string $email): ?User
    {
        // ...
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof ClassMethod) {
            return null;
        }

        $methodName = $this->getName($node);
        if (!$this->shouldRenameMethod($methodName)) {
            return null;
        }

        $returnType = $node->getReturnType();
        if (!$this->isEligibleReturnType($returnType)) {
            return null;
        }

        $newMethodName = $this->createNewMethodName($methodName);
        $node->name->name = $newMethodName;

        return $node;
    }

    /**
     * Checks if the method name should be renamed from 'find*' to 'get*'
     */
    private function shouldRenameMethod(?string $methodName): bool
    {
        return $methodName !== null && str_starts_with($methodName, self::FIND_PREFIX);
    }

    /**
     * Checks if the return type is eligible for renaming (non-nullable entity type)
     */
    private function isEligibleReturnType(?Node $returnType): bool
    {
        if ($returnType === null) {
            return false;
        }

        // Skip nullable types (should remain as 'find*')
        if ($returnType instanceof NullableType) {
            return false;
        }

        // Skip union types (should remain as 'find*')
        if ($returnType instanceof UnionType) {
            return false;
        }

        // Only process named types (classes/interfaces)
        if (!$returnType instanceof Name) {
            return false;
        }

        // Skip primitive types
        $returnTypeName = $returnType->toString();
        return !$this->isPrimitiveType($returnTypeName);
    }

    /**
     * Checks if the given type name is a primitive type
     */
    private function isPrimitiveType(string $typeName): bool
    {
        return in_array(strtolower($typeName), self::PRIMITIVE_TYPES, true);
    }

    /**
     * Creates the new method name by replacing 'find' prefix with 'get'
     * @return non-empty-string
     */
    private function createNewMethodName(string $methodName): string
    {
        return self::GET_PREFIX . substr($methodName, strlen(self::FIND_PREFIX));
    }
}
