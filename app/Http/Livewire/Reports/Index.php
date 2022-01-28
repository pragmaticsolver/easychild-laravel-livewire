<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;

class Index extends Component
{
    public $view = 'planning';

    protected $queryString = [
        'view' => ['except' => 'planning'],
    ];

    public function getViewTypeProperty()
    {
        $viewTypes = [];
        $viewTypes['planning'] = [
            'text' => trans('reports.report_types.planning'),
            'href' => route('reports.index', 'view=planning')
        ];
        $viewTypes['attendance'] = [
            'text' => trans('reports.report_types.attendance'),
            'href' => route('reports.index', 'view=attendance')
        ];
        $viewTypes['absence'] = [
            'text' => trans('reports.report_types.absence'),
            'href' => route('reports.index', 'view=absence')
        ];
        return $viewTypes;
    }

    public function canShowDownloadButton()
    {
        return in_array($this->view, ['planning', 'attendance']);
    }

    public function download()
    {
        if (! $this->canShowDownloadButton()) {
            return;
        }

        $this->emitTo("reports.planning", 'downloadExcel');
    }

    public function render()
    {
        return view('livewire.reports.index');
    }
}
