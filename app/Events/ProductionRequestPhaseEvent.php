<?php

namespace App\Events;

use App\Models\ProductionRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class ProductionRequestPhaseEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $type;
    public ProductionRequest $pr;
    public array $context;

    public function __construct(string $type, ProductionRequest $pr, array $context = [])
    {
        $this->type = $type;
        $this->pr = $pr;
        $this->context = $context;
    }
}
