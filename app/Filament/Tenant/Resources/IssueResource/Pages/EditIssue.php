<?php

namespace App\Filament\Tenant\Resources\IssueResource\Pages;

use App\Filament\Tenant\Resources\IssueResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIssue extends EditRecord
{
    protected static string $resource = IssueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
