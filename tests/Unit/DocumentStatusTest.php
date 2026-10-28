<?php

use App\Enum\DocumentStatus;
use Filament\Support\Icons\Heroicon;

test('every status resolves a label, color and a valid heroicon', function () {
    foreach (DocumentStatus::cases() as $status) {
        expect($status->getLabel())->toBeString()->not->toBe('')
            ->and($status->getColor())->not->toBeNull()
            ->and($status->getIcon())->toBeInstanceOf(Heroicon::class);
    }
});
