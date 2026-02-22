<div>
    {{ $this->form }}

    @if ($error)
        <div class="mt-4 p-4 bg-danger/10 text-danger rounded-lg">
            {{ $error }}
        </div>
    @endif

    @if ($document)
        <div class="mt-6 space-y-6">
            <div class="p-4 bg-primary/10 rounded-lg">
                <h3 class="text-lg font-semibold text-primary">{{ __('Document information') }}</h3>
                <div class="grid grid-cols-2 gap-4 mt-2">
                    <div>
                        <span class="text-sm text-gray-500">{{ __('Document number') }}:</span>
                        <p class="font-medium">{{ $document->document_number }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">{{ __('Case number') }}:</span>
                        <p class="font-medium">{{ $document->case_number }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">{{ __('Subject') }}:</span>
                        <p class="font-medium">{{ $document->subject }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">{{ __('Document type') }}:</span>
                        <p class="font-medium">{{ $document->documentType?->name }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">{{ __('Status') }}:</span>
                        <p class="font-medium">{{ $document->status->label() }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">{{ __('Priority') }}:</span>
                        <p class="font-medium">{{ $document->priority?->name }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">{{ __('Reception date') }}:</span>
                        <p class="font-medium">{{ $document->reception_date->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">{{ __('Origin') }}:</span>
                        <p class="font-medium">{{ $document->origen }}</p>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ __('Document movements') }}</h3>
                @if ($document->movements->isEmpty())
                    <p class="mt-2 text-gray-500">{{ __('No movements registered') }}</p>
                @else
                    <div class="mt-4 space-y-4">
                        @foreach ($document->movements->sortBy('receipt_date') as $movement)
                            <div class="p-4 border rounded-lg">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $movement->action->getColor() }}/10 text-{{ $movement->action->getColor() }}">
                                            {{ $movement->action->label() }}
                                        </span>
                                        <p class="mt-2 text-sm text-gray-500">
                                            <span class="font-medium">{{ __('From') }}:</span> {{ $movement->originOffice?->name ?? 'N/A' }}
                                            ({{ $movement->originUser?->name ?? 'N/A' }})
                                        </p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            <span class="font-medium">{{ __('To') }}:</span> {{ $movement->destinationOffice?->name ?? 'N/A' }}
                                            ({{ $movement->destinationUser?->name ?? 'N/A' }})
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm text-gray-500">{{ __('Date') }}:</span>
                                        <p class="text-sm font-medium">{{ $movement->receipt_date->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                                @if ($movement->indication)
                                    <div class="mt-2">
                                        <span class="text-sm text-gray-500">{{ __('Indication') }}:</span>
                                        <p class="text-sm">{{ $movement->indication }}</p>
                                    </div>
                                @endif
                                @if ($movement->observation)
                                    <div class="mt-2">
                                        <span class="text-sm text-gray-500">{{ __('Observation') }}:</span>
                                        <p class="text-sm">{{ $movement->observation }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
