<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;

class KpiCard extends Component
{
    public string $label;
    public string $value;
    public ?string $subtext;

    public function __construct(string $label, string $value, ?string $subtext = null)
    {
        $this->label = $label;
        $this->value = $value;
        $this->subtext = $subtext;
    }

    public function render()
    {
        return view('components.dashboard.kpi-card');
    }
}
