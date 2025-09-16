<?php

namespace Rector\Tests\TypeDeclaration\Rector\RenemameFindAndGetMethodCallRector\Fixture;

abstract class SomeEntityRepository {
    abstract public function findById(int $id): SomeEntity;
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\RenemameFindAndGetMethodCallRector\Fixture;

abstract class SomeEntityRepository {
    abstract public function getById(int $id): SomeEntity;
}

?>
