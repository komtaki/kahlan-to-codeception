<?php

use Zeiri4\Domains\BackyardMasterData\Service\CreateCsvService;
use Zeiri4\Domains\BackyardMasterData\Data\Table;
use Zeiri4\Domains\BackyardMasterData\Data\TableColumn;
use Zeiri4\Domains\BackyardMasterData\Repository\SourceRepositoryPort;
use Zeiri4\Domains\BackyardMasterData\Data\UpdatePayloadPort;

describe(CreateCsvService::class, function () {
    given('service', function () {
        return new CreateCsvService(
            $this->source_repository_mock
        );
    });

    given('source_repository_mock', function () {
        return new class() implements SourceRepositoryPort
        {
            public function fetchByPkList(Table $table, array $primary_keys): array
            {
                return [];
            }
            public function create(UpdatePayloadPort $record): void
            {
            }
            public function update(UpdatePayloadPort $record): void
            {
            }
            public function exists(UpdatePayloadPort $record): bool
            {
                return true;
            }

            public function fetchAll(Table $table): array
            {
                return [
                    ['id' => 1, 'name' => 'テスト1'],
                ];
            }
        };
    });

    given('className', function () {
        return 'sss';
    });

    given('count', function () {
        return 1;
    });

    given('table', function () {
        return new Table(
            'test',
            [
                new TableColumn('id', 'int', true),
            ],
            'id',
            null
        );
    });

    describe('->exec', function () {
        context('データが存在している', function () {
            it('結果を返す', function () {
                $result = (new CreateCsvService($this->source_repository_mock))->exec($this->table);
                expect($result->getFileName())->toBe('test');
                expect($result->getFileContent())->toBe("id,name\n1,テスト1\n2,テスト2\n3,テスト3\n");
            });
        });
    });
});
