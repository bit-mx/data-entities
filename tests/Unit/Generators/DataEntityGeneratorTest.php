<?php

declare(strict_types=1);

use BitMx\DataEntities\Generators\DataEntityGenerator;
use BitMx\DataEntities\Introspection\ProcedureParameter;

it('generates a data entity class from introspected parameters', function () {
    $contents = (new DataEntityGenerator)->generate(
        namespace: 'App\\DataEntities',
        class: 'CreatePostDataEntity',
        procedure: 'dbo.spCreatePost',
        parameters: [
            new ProcedureParameter('title', 'NVARCHAR(100)', isOutput: false, isInput: true),
            new ProcedureParameter('is_active', 'BIT', isOutput: false, isInput: true),
            new ProcedureParameter('new_id', 'INT', isOutput: true, isInput: false),
        ],
    );

    expect($contents)
        ->toContain('namespace App\\DataEntities;')
        ->toContain('class CreatePostDataEntity extends DataEntity')
        ->toContain('protected string $title,')
        ->toContain('protected bool $isActive,')
        ->toContain("return 'dbo.spCreatePost';")
        ->toContain("'title' => \$this->title,")
        ->toContain("'is_active' => \$this->isActive,")
        ->toContain("'is_active' => 'bool',")
        ->toContain("'new_id' => 'INT',");
});
